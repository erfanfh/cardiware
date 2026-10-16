<?php

namespace App\Application\Launcher\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Catalog\Enums\ProductSaleItemStatusEnum;
use App\Domain\Catalog\Enums\ProductStatusEnum;
use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Identity\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Pagination\Paginator as PaginatorState;
use Illuminate\Support\Facades\Cache;

/**
 * Catalog content is cached per (search, page, perPage, owned unavailable IDs).
 * Users with the same eligible retired items can share the expensive product/media/
 * discount snapshot. The per-user purchaseAccesses relation is always loaded live
 * and attached to the cached models.
 * Invalidated by LauncherCacheInvalidator on any sale item / product change (see
 * BumpLauncherManifestRevisionAction and LauncherCacheObserver).
 */
class GetLauncherCatalogAction
{
    public function execute(User $user, ?string $search, int $perPage): LengthAwarePaginator
    {
        $search = trim((string) $search);
        $page = max(1, (int) PaginatorState::resolveCurrentPage());
        // Resolve entitlements live so expiry/revocation cannot leave a retired item
        // visible through a shared catalog cache. Keep pagination and search in SQL.
        $ownedUnavailableIds = PurchaseAccess::query()
            ->where('user_id', $user->getKey())
            ->launcherUsable()
            ->whereHas('productSaleItem', fn (Builder $query): Builder => $query
                ->where('launcher_enabled', true)
                ->where('status', ProductSaleItemStatusEnum::UNAVAILABLE->value))
            ->distinct()
            ->orderBy('product_sale_item_id')
            ->pluck('product_sale_item_id')
            ->all();
        $cacheKey = CacheKey::launcherCatalogPage($search, $page, $perPage);
        if ($ownedUnavailableIds !== []) {
            $cacheKey .= ':owned:'.hash('sha256', implode(',', $ownedUnavailableIds));
        }

        /** @var array{items: Collection<int, ProductSaleItem>, total: int} $cached */
        $cached = Cache::tags([CacheTag::LAUNCHER, CacheTag::LAUNCHER_CATALOG])->remember(
            $cacheKey,
            CacheTtl::short(),
            function () use ($search, $perPage, $page, $ownedUnavailableIds): array {
                $paginator = $this->baseQuery($ownedUnavailableIds)
                    ->with([
                        'product.picture.primaryVariant',
                        'launcherHero.primaryVariant',
                        'discount',
                    ])
                    ->when($search !== '', function (Builder $query) use ($search): void {
                        $query->where(function (Builder $query) use ($search): void {
                            $query->where('launcher_title', 'like', "%{$search}%")
                                ->orWhere('title', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%")
                                ->orWhereHas('product', fn(Builder $product): Builder => $product->where('title', 'like', "%{$search}%"));
                        });
                    })
                    ->orderBy('launcher_sort_order')
                    ->orderByDesc('id')
                    ->paginate($perPage, ['*'], 'page', $page);

                return [
                    'items' => $paginator->getCollection(),
                    'total' => $paginator->total(),
                ];
            },
        );

        $items = $cached['items'];
        $this->attachPurchaseAccesses($user, $items);

        return new Paginator(
            $items,
            $cached['total'],
            $perPage,
            $page,
            [
                'path' => PaginatorState::resolveCurrentPath(),
                'pageName' => 'page',
            ],
        );
    }

    public function find(User $user, int $saleItemId): ProductSaleItem
    {
        $revision = (int) ProductSaleItem::query()
            ->whereKey($saleItemId)
            ->value('manifest_revision');

        /** @var ProductSaleItem|null $saleItem */
        $saleItem = Cache::tags([
            CacheTag::LAUNCHER,
            CacheTag::LAUNCHER_CATALOG,
            CacheTag::launcherSaleItem($saleItemId),
        ])->remember(
            CacheKey::launcherCatalogItem($saleItemId, $revision),
            CacheTtl::short(),
            fn(): ?ProductSaleItem => ProductSaleItem::query()
                ->whereKey($saleItemId)
                ->where('launcher_enabled', true)
                ->whereHas(
                    'product',
                    fn(Builder $query): Builder => $query->where('status', ProductStatusEnum::ACTIVE->value)
                )
                ->with([
                    'product.picture.primaryVariant',
                    'launcherHero.primaryVariant',
                    'discount',
                    'manifest',
                ])
                ->first(),
        );

        if (! $saleItem instanceof ProductSaleItem) {
            // Same outcome the previous firstOrFail() produced (404 through the handler).
            throw (new ModelNotFoundException())->setModel(ProductSaleItem::class, [$saleItemId]);
        }

        $this->attachPurchaseAccesses($user, new Collection([$saleItem]));

        return $saleItem;
    }

    /** @param array<int, int|string> $ownedUnavailableIds */
    private function baseQuery(array $ownedUnavailableIds): Builder
    {
        return ProductSaleItem::query()
            ->where('launcher_enabled', true)
            ->where(function (Builder $query) use ($ownedUnavailableIds): void {
                $query->where('status', ProductSaleItemStatusEnum::AVAILABLE->value)
                    ->orWhereIn('id', $ownedUnavailableIds);
            })
            ->whereHas(
                'product',
                fn(Builder $query): Builder => $query->where('status', ProductStatusEnum::ACTIVE->value)
            );
    }

    /**
     * Exactly the relation the resource used to get through eager loading: the user's
     * accesses for these sale items, all global scopes removed, newest first, with the
     * order metadata and free-claim flag isUsableForLauncher() needs.
     *
     * @param Collection<int, ProductSaleItem> $saleItems
     */
    private function attachPurchaseAccesses(User $user, Collection $saleItems): void
    {
        if ($saleItems->isEmpty()) {
            return;
        }

        $accesses = PurchaseAccess::query()
            ->withoutGlobalScopes()
            ->where('user_id', $user->getKey())
            ->whereIn('product_sale_item_id', $saleItems->modelKeys())
            ->with([
                'order:id,metadata',
                'productSaleItem:id,launcher_free_claim_enabled',
            ])
            ->latest('id')
            ->get()
            ->groupBy('product_sale_item_id');

        foreach ($saleItems as $saleItem) {
            $saleItem->setRelation(
                'purchaseAccesses',
                $accesses->get($saleItem->getKey(), new Collection())->values(),
            );
        }
    }
}

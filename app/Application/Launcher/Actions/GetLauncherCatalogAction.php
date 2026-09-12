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
 * The public part of a catalog page (sale items + product/media/discount/manifest) is
 * identical for every user and is what makes this endpoint expensive, so it is cached
 * per (search, page, perPage). The one per-user relation the resource reads --
 * purchaseAccesses -- is loaded live with the same constraints it always had and
 * attached to the cached models, so the response is what the uncached query produced.
 * Invalidated by LauncherCacheInvalidator on any sale item / product change (see
 * BumpLauncherManifestRevisionAction and LauncherCacheObserver).
 */
class GetLauncherCatalogAction
{
    public function execute(User $user, ?string $search, int $perPage): LengthAwarePaginator
    {
        $search = trim((string) $search);
        $page = max(1, (int) PaginatorState::resolveCurrentPage());

        /** @var array{items: Collection<int, ProductSaleItem>, total: int} $cached */
        $cached = Cache::tags([CacheTag::LAUNCHER, CacheTag::LAUNCHER_CATALOG])->remember(
            CacheKey::launcherCatalogPage($search, $page, $perPage),
            CacheTtl::short(),
            function () use ($search, $perPage, $page): array {
                $paginator = $this->baseQuery()
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

    private function baseQuery(): Builder
    {
        return ProductSaleItem::query()
            ->where('launcher_enabled', true)
            ->where('status', ProductSaleItemStatusEnum::AVAILABLE->value)
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

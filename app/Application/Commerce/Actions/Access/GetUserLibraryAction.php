<?php

namespace App\Application\Commerce\Actions\Access;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Domain\Catalog\Scopes\ActiveProductScope;
use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Identity\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Pagination\Paginator as PaginatorState;
use Illuminate\Support\Facades\Cache;

class GetUserLibraryAction
{
    public function __construct(
        private readonly HydratePurchaseAccessFileDownloadStatsAction $hydrateDownloadStatsAction,
    )
    {
    }

    public function execute(
        User $user,
        int  $perPage = 20,
    ): LengthAwarePaginator
    {
        // The access rows + product/sale item/file relations are the expensive part and
        // change only when the user buys something or an admin edits a sale item; both
        // flush this entry (LauncherCacheObserver / BumpLauncherManifestRevisionAction).
        // Download statistics are hydrated live below, exactly as before, so per-file
        // counters are never stale.
        $page = max(1, (int) PaginatorState::resolveCurrentPage());

        /** @var array{items: Collection<int, PurchaseAccess>, total: int} $cached */
        $cached = Cache::tags([
            CacheTag::LAUNCHER,
            CacheTag::LAUNCHER_LIBRARY,
            CacheTag::launcherUser($user->getKey()),
        ])->remember(
            CacheKey::launcherLibrary($user->getKey(), $page, $perPage),
            now()->addMinutes(5),
            function () use ($user, $perPage, $page): array {
                $paginator = $this->query($user, $perPage, $page);

                return [
                    'items' => $paginator->getCollection(),
                    'total' => $paginator->total(),
                ];
            },
        );

        $purchaseAccesses = new Paginator(
            $cached['items'],
            $cached['total'],
            $perPage,
            $page,
            ['path' => PaginatorState::resolveCurrentPath(), 'pageName' => 'page'],
        );

        $this->hydrateDownloadStatsAction->execute(
            $purchaseAccesses->getCollection()
        );

        return $purchaseAccesses;
    }

    private function query(User $user, int $perPage, int $page): LengthAwarePaginator
    {
        return PurchaseAccess::query()
            ->where('user_id', $user->getKey())
            ->launcherUsable()
            ->whereHas(
                'productSaleItem',
                fn($query) => $query->where('launcher_enabled', true)
            )
            ->with([
                'order:id,metadata',
                'product' => fn($relation) => $relation
                    ->withoutGlobalScope(ActiveProductScope::class)
                    ->select(['id', 'title', 'slug', 'picture_id']),

                'productSaleItem:id,product_id,sku,title,edition_key,version,description,launcher_enabled,launcher_free_claim_enabled,launcher_title,launcher_excerpt',

                'productSaleItem.files' => function (
                    HasMany $relation,
                ): void {
                    $relation
                        ->where('is_active', true)
                        ->select([
                            'id',
                            'product_sale_item_id',
                            'title',
                            'sort_order',
                            'file_name',
                            'size_bytes',
                            'sha256',
                        ])
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
            ])
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}

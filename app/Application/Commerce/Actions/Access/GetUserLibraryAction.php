<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Identity\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        $purchaseAccesses = PurchaseAccess::query()
            ->where('user_id', $user->getKey())
            ->launcherUsable()
            ->whereHas(
                'productSaleItem',
                fn($query) => $query->where('launcher_enabled', true)
            )
            ->with([
                'order:id,metadata',
                'product:id,title,slug,picture_id',

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
            ->paginate($perPage);

        $this->hydrateDownloadStatsAction->execute(
            $purchaseAccesses->getCollection()
        );

        return $purchaseAccesses;
    }
}

<?php

namespace App\Application\Commerce\Actions\Access;

use App\Domain\Commerce\Models\PurchaseAccess;
use App\Domain\Identity\Models\User;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GetUserLibraryItemAction
{
    public function __construct(
        private readonly HydratePurchaseAccessFileDownloadStatsAction $hydrateDownloadStatsAction,
    )
    {
    }

    public function execute(
        User $user,
        int  $purchaseAccessId,
    ): PurchaseAccess
    {
        $purchaseAccess = PurchaseAccess::query()
            ->whereKey($purchaseAccessId)
            ->where('user_id', $user->getKey())
            ->usable()
            ->with([
                'product:id,title,slug,picture_id',

                'productSaleItem:id,product_id,sku,description',

                'productSaleItem.files' => function (
                    HasMany $relation,
                ): void {
                    $relation
                        ->select([
                            'id',
                            'product_sale_item_id',
                            'title',
                            'sort_order',
                        ])
                        ->orderBy('sort_order')
                        ->orderBy('id');
                },
            ])
            ->firstOrFail();

        $this->hydrateDownloadStatsAction->execute(
            collect([$purchaseAccess])
        );

        return $purchaseAccess;
    }
}

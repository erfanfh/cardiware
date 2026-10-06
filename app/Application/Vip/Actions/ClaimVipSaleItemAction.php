<?php

namespace App\Application\Vip\Actions;

use App\Domain\Catalog\Models\ProductSaleItem;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;

class ClaimVipSaleItemAction
{
    public function execute(User $user, ProductSaleItem $saleItem): void
    {
        abort_unless($user->hasActiveVipSubscription(), 403);
        abort_unless(
            app(GetVipDownloadableSaleItemsAction::class)->query()
                ->whereKey($saleItem->getKey())->exists(),
            404,
        );
        abort_unless($saleItem->product()->withoutGlobalScopes()->first()?->isPubliclyVisible(), 404);

        if ($user->purchaseAccesses()->usable()
            ->where('product_sale_item_id', $saleItem->getKey())->exists()) {
            return;
        }

        DB::table('vip_sale_item_claims')->insertOrIgnore([
            'user_id' => $user->getKey(),
            'product_sale_item_id' => $saleItem->getKey(),
            'product_title' => $saleItem->product->title,
            'sale_item_title' => $saleItem->title,
            'claimed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        abort_unless(DB::table('vip_sale_item_claims')
            ->where('user_id', $user->getKey())
            ->where('product_sale_item_id', $saleItem->getKey())
            ->exists(), 500);
    }
}

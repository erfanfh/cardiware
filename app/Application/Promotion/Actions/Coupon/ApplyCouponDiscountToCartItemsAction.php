<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;

class ApplyCouponDiscountToCartItemsAction
{
    /**
     * @param array<int, int> $itemDiscounts
     */
    public function execute(Cart $cart, array $itemDiscounts = []): void
    {
        $cart->loadMissing('items');

        foreach ($cart->items as $item) {
            if (!$item instanceof CartItem) {
                continue;
            }

            $itemTotalAmount = max(0, (int)$item->total_amount);

            $couponDiscountAmount = max(0, (int)($itemDiscounts[$item->id] ?? 0));
            $couponDiscountAmount = min($couponDiscountAmount, $itemTotalAmount);

            $item->forceFill([
                'coupon_discount_amount' => $couponDiscountAmount,
                'final_total_amount' => max(0, $itemTotalAmount - $couponDiscountAmount),
            ])->save();
        }
    }
}

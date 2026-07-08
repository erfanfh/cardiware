<?php

namespace App\Application\Promotion\Actions\Coupon;

use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\OrderItem;

class ApplyCouponDiscountToOrderItemsAction
{
    /**
     * @param array<int, int> $itemDiscounts
     */
    public function execute(Order $order, array $itemDiscounts = []): void
    {
        $order->loadMissing('items');

        foreach ($order->items as $item) {
            if (!$item instanceof OrderItem) {
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

<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;

class RecalculateCartTotalsAction
{
    /**
     * Recalculate cart totals from its items.
     */
    public function execute(Cart $cart): Cart
    {
        $items = $cart->items()->get();

        $subtotalAmount = (int)$items->sum(function (CartItem $item): int {
            return (int)(
                $item->subtotal_amount
                ?? ((int)$item->unit_price * (int)$item->quantity)
            );
        });

        $discountAmount = (int)$items->sum('discount_amount');

        $cart->forceFill([
            'subtotal_amount' => $subtotalAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => max(0, $subtotalAmount - $discountAmount),
        ])->save();

        return $cart->refresh();
    }
}

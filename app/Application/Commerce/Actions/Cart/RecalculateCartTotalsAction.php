<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Domain\Commerce\Models\Cart;

class RecalculateCartTotalsAction
{
    /**
     * Recalculate cart totals from its items.
     */
    public function execute(Cart $cart): Cart
    {
        $items = $cart->items()->get();

        $subtotalAmount = (int)$items->sum('subtotal_amount');
        $discountAmount = (int)$items->sum('discount_amount');

        $cart->forceFill([
            'subtotal_amount' => $subtotalAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => max(0, $subtotalAmount - $discountAmount),
        ])->save();

        return $cart->refresh();
    }
}

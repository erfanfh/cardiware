<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Domain\Commerce\Models\CartItem;

class RecalculateCartItemTotalsAction
{
    /**
     * Recalculate totals for a cart item.
     */
    public function execute(CartItem $cartItem): CartItem
    {
        $subtotalAmount = $cartItem->unit_price * $cartItem->quantity;
        $discountAmount = $cartItem->discount_amount;

        $cartItem->forceFill([
            'subtotal_amount' => $subtotalAmount,
            'total_amount' => max(0, $subtotalAmount - $discountAmount),
        ])->save();

        return $cartItem->refresh();
    }
}

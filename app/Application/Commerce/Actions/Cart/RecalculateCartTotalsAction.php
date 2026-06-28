<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;

class RecalculateCartTotalsAction
{
    public function __construct(
        private readonly RecalculateCartItemTotalsAction $recalculateCartItemTotalsAction,
    )
    {
    }

    /**
     * Recalculate cart item prices from current purchasable data,
     * then recalculate cart totals from updated items.
     */
    public function execute(Cart $cart): Cart
    {
        $cart->loadMissing([
            'items.purchasable',
        ]);

        foreach ($cart->items as $item) {
            if (!$item instanceof CartItem) {
                continue;
            }

            $this->recalculateCartItemTotalsAction->execute($item);
        }

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

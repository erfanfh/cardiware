<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Domain\Commerce\Models\Cart;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class ClearCartAction
{
    public function __construct(
        private readonly RecalculateCartTotalsAction $recalculateCartTotalsAction,
    )
    {
    }

    /**
     * Clear all items from the cart.
     * @throws Throwable
     */
    public function execute(int $userId, Cart $cart): Cart
    {
        return DB::transaction(function () use ($userId, $cart) {
            if ((int)$cart->user_id !== $userId) {
                throw new DomainException('Cart does not belong to the user.');
            }

            if (!$cart->isEditable()) {
                throw new DomainException('Cart is not editable.');
            }

            $cart->items()
                ->whereNotNull('parent_cart_item_id')
                ->delete();

            $cart->items()
                ->whereNull('parent_cart_item_id')
                ->delete();

            return $this->recalculateCartTotalsAction->execute($cart);
        });
    }
}

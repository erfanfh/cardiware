<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class RemoveCartItemAction
{
    public function __construct(
        private readonly RecalculateCartTotalsAction $recalculateCartTotalsAction,
    )
    {
    }

    /**
     * Remove an item from the cart.
     * @throws Throwable
     */
    public function execute(int $userId, CartItem $cartItem): Cart
    {
        return DB::transaction(function () use ($userId, $cartItem) {
            $cart = $cartItem->cart;

            if ((int)$cart->user_id !== $userId) {
                throw new DomainException('Cart item does not belong to the user.');
            }

            if (!$cart->isEditable()) {
                throw new DomainException('Cart is not editable.');
            }

            $cartItem->delete();

            return $this->recalculateCartTotalsAction->execute($cart);
        });
    }
}

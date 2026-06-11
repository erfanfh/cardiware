<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\DTOs\CartOwnerDTO;
use App\Domain\Commerce\Models\CartItem;
use DomainException;
use Throwable;

class AdjustCartItemQuantityAction
{
    public function __construct(
        private readonly UpdateCartItemQuantityAction $updateCartItemQuantityAction,
    )
    {
    }

    /**
     * Adjust cart item quantity by given delta.
     *
     * @throws Throwable
     */
    public function execute(CartItem $cartItem, int $delta, ?CartOwnerDTO $owner = null): CartItem
    {
        if ($delta === 0) {
            return $cartItem->refresh();
        }

        $currentQuantity = (int)($cartItem->quantity ?? 1);
        $newQuantity = $currentQuantity + $delta;

        if ($newQuantity < 1) {
            throw new DomainException('Quantity must be at least 1.');
        }

        return $this->updateCartItemQuantityAction->execute(
            cartItem: $cartItem,
            quantity: $newQuantity,
            owner: $owner,
        );
    }
}

<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\DTOs\CartOwnerDTO;
use App\Domain\Commerce\Models\Cart;
use DomainException;

class EnsureCartBelongsToOwnerAction
{
    /**
     * Ensure the cart belongs to the given owner.
     */
    public function execute(CartOwnerDTO $owner, Cart $cart): void
    {
        if (!$owner->owns($cart)) {
            throw new DomainException('Cart does not belong to the current owner.');
        }
    }
}

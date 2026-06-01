<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\Contracts\Purchasable;
use App\Domain\Commerce\Models\CartItem;
use DomainException;
use Illuminate\Database\Eloquent\Model;

class CalculateCartItemQuantityAction
{
    /**
     * Calculate final cart item quantity.
     *
     * @param Purchasable&Model $purchasable
     */
    public function execute(
        Purchasable&Model $purchasable,
        int               $requestedQuantity,
        ?CartItem         $currentCartItem = null,
    ): int
    {
        $finalQuantity = $requestedQuantity;

        if ($currentCartItem instanceof CartItem) {
            $finalQuantity += $currentCartItem->quantity;
        }

        $maxQuantityPerOrder = $purchasable->getMaxQuantityPerOrder();

        if ($maxQuantityPerOrder !== null && $finalQuantity > $maxQuantityPerOrder) {
            throw new DomainException('Requested quantity exceeds the allowed maximum per order.');
        }

        return $finalQuantity;
    }
}

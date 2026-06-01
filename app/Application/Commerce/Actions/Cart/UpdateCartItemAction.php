<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\Contracts\Purchasable;
use App\Domain\Commerce\Models\CartItem;
use Illuminate\Database\Eloquent\Model;

class UpdateCartItemAction
{
    /**
     * Update an existing cart item.
     *
     * @param Purchasable&Model $purchasable
     * @param array<string, mixed> $metadata
     */
    public function execute(
        CartItem          $cartItem,
        Purchasable&Model $purchasable,
        int               $quantity,
        array             $metadata = [],
    ): CartItem
    {
        $cartItem->forceFill([
            'quantity' => $quantity,
            'unit_price' => $purchasable->getPurchasablePrice(),
            'snapshot' => $purchasable->getPurchasableSnapshot(),
            'metadata' => array_merge($cartItem->metadata ?? [], $metadata),
        ])->save();

        return $cartItem->refresh();
    }
}

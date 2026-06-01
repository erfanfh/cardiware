<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\Contracts\Purchasable;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use Illuminate\Database\Eloquent\Model;

class CreateCartItemAction
{
    /**
     * Create a new cart item.
     *
     * @param Purchasable&Model $purchasable
     * @param array<string, mixed> $metadata
     */
    public function execute(
        Cart              $cart,
        Purchasable&Model $purchasable,
        int               $quantity,
        ?CartItem         $parentCartItem = null,
        array             $metadata = [],
    ): CartItem
    {
        return CartItem::query()->create([
            'cart_id' => $cart->id,
            'parent_cart_item_id' => $parentCartItem?->id,
            'purchasable_type' => $purchasable->getMorphClass(),
            'purchasable_id' => $purchasable->getKey(),
            'quantity' => $quantity,
            'unit_price' => $purchasable->getPurchasablePrice(),
            'subtotal_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'snapshot' => $purchasable->getPurchasableSnapshot(),
            'metadata' => $metadata,
        ]);
    }
}

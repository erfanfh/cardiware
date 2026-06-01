<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\Contracts\Purchasable;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use Illuminate\Database\Eloquent\Model;

class FindMatchingCartItemAction
{
    /**
     * Find an existing matching cart item.
     *
     * @param Purchasable&Model $purchasable
     */
    public function execute(
        Cart              $cart,
        Purchasable&Model $purchasable,
        ?CartItem         $parentCartItem = null,
    ): ?CartItem
    {
        return CartItem::query()
            ->where('cart_id', $cart->id)
            ->where('parent_cart_item_id', $parentCartItem?->id)
            ->where('purchasable_type', $purchasable->getMorphClass())
            ->where('purchasable_id', $purchasable->getKey())
            ->first();
    }
}

<?php

namespace App\Application\Commerce\Actions\Order;

use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\Order;

class CreateOrderItemsFromCartAction
{
    public function __construct(
        private readonly CreateOrderItemFromCartItemAction $createOrderItemFromCartItemAction,
    )
    {
    }

    /**
     * Create order items from cart items.
     *
     * @return array<int, int> Cart item ID to order item ID map.
     */
    public function execute(Order $order, Cart $cart): array
    {
        $cart->loadMissing('items');

        $cartItemToOrderItemMap = [];

        $rootItems = $cart->items
            ->whereNull('parent_cart_item_id')
            ->sortBy('id')
            ->values();

        foreach ($rootItems as $cartItem) {
            $orderItem = $this->createOrderItemFromCartItemAction->execute(
                order: $order,
                cartItem: $cartItem,
            );

            $cartItemToOrderItemMap[$cartItem->id] = $orderItem->id;
        }

        $childItems = $cart->items
            ->whereNotNull('parent_cart_item_id')
            ->sortBy('id')
            ->values();

        foreach ($childItems as $cartItem) {
            $orderItem = $this->createOrderItemFromCartItemAction->execute(
                order: $order,
                cartItem: $cartItem,
                cartItemToOrderItemMap: $cartItemToOrderItemMap,
            );

            $cartItemToOrderItemMap[$cartItem->id] = $orderItem->id;
        }

        return $cartItemToOrderItemMap;
    }
}

<?php

namespace App\Application\Commerce\Actions\Order;

use App\Application\Commerce\Support\OrderItemReportDataFactory;
use App\Domain\Commerce\Models\CartItem;
use App\Domain\Commerce\Models\Order;
use App\Domain\Commerce\Models\OrderItem;
use Illuminate\Contracts\Container\BindingResolutionException;

class CreateOrderItemFromCartItemAction
{
    /**
     * Create an order item from a cart item.
     *
     * @param array<int, int> $cartItemToOrderItemMap
     * @throws BindingResolutionException
     */
    public function execute(
        Order    $order,
        CartItem $cartItem,
        array    $cartItemToOrderItemMap = [],
    ): OrderItem
    {
        $parentOrderItemId = null;

        if ($cartItem->parent_cart_item_id !== null) {
            $parentOrderItemId = $cartItemToOrderItemMap[$cartItem->parent_cart_item_id] ?? null;
        }

        $reportData = app(OrderItemReportDataFactory::class)->make($cartItem->purchasable);

        return OrderItem::query()->create([
            'order_id' => $order->id,
            'parent_order_item_id' => $parentOrderItemId,
            'purchasable_type' => $cartItem->purchasable_type,
            'purchasable_id' => $cartItem->purchasable_id,
            'product_id' => $reportData['product_id'],
            'store_id' => $reportData['store_id'],
            'quantity' => $cartItem->quantity,
            'unit_price' => $cartItem->unit_price,
            'subtotal_amount' => $cartItem->subtotal_amount,
            'discount_amount' => $cartItem->discount_amount,
            'total_amount' => $cartItem->total_amount,
            'snapshot' => $cartItem->snapshot,
            'metadata' => $cartItem->metadata,
        ]);
    }
}

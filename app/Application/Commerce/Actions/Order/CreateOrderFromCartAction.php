<?php

namespace App\Application\Commerce\Actions\Order;

use App\Application\Commerce\Actions\Cart\MarkCartAsCheckoutStartedAction;
use App\Application\Commerce\Actions\Cart\MarkCartAsConvertedAction;
use App\Application\Commerce\Actions\Cart\RecalculateCartTotalsAction;
use App\Domain\Commerce\Enums\OrderStatusEnum;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\Order;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateOrderFromCartAction
{
    public function __construct(
        private readonly MarkCartAsCheckoutStartedAction    $markCartAsCheckoutStartedAction,
        private readonly MarkCartAsConvertedAction          $markCartAsConvertedAction,
        private readonly RecalculateCartTotalsAction        $recalculateCartTotalsAction,
        private readonly ValidateCartForOrderAction         $validateCartForOrderAction,
        private readonly ValidateCartItemsForCheckoutAction $validateCartItemsForCheckoutAction,
        private readonly GenerateOrderNumberAction          $generateOrderNumberAction,
        private readonly CreateOrderItemsFromCartAction     $createOrderItemsFromCartAction,
    )
    {
    }

    /**
     * Create an order from a cart.
     *
     * @param array<string, mixed> $metadata
     * @throws Throwable
     */
    public function execute(Cart $cart, array $metadata = []): Order
    {
        return DB::transaction(function () use ($cart, $metadata) {
            $cart = $cart->refresh();

            if (!$cart->isEditable()) {
                throw new DomainException('Only active carts can be converted to order.');
            }

            /*
             * Important:
             * Recalculate while cart is still ACTIVE.
             * Coupon validation depends on editable cart.
             */
            $cart = $this->recalculateCartTotalsAction->execute($cart);

            /*
             * Now move cart to checkout started.
             * ValidateCartForOrderAction expects this status.
             */
            $cart = $this->markCartAsCheckoutStartedAction->execute($cart);

            $this->validateCartForOrderAction->execute($cart);

            $this->validateCartItemsForCheckoutAction->execute($cart);

            $order = Order::query()->create([
                'user_id' => $cart->user_id,
                'cart_id' => $cart->id,
                'status' => OrderStatusEnum::PENDING_PAYMENT,

                'subtotal_amount' => $cart->subtotal_amount,
                'discount_amount' => $cart->discount_amount,
                'total_amount' => $cart->total_amount,

                'coupon_id' => $cart->coupon_id,
                'coupon_code' => $cart->coupon_code,
                'coupon_eligible_amount' => $cart->coupon_eligible_amount,
                'coupon_discount_amount' => $cart->coupon_discount_amount,

                'metadata' => $metadata,
            ]);

            $order->update([
                'order_number' => $this->generateOrderNumberAction->execute($order),
            ]);

            $this->createOrderItemsFromCartAction->execute(
                order: $order,
                cart: $cart,
            );

            $this->markCartAsConvertedAction->execute($cart);

            return $order->load([
                'items.children',
                'items.purchasable',
            ]);
        });
    }
}

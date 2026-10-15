<?php

namespace App\Application\Commerce\Actions\Order;

use App\Application\Commerce\Actions\Cart\MarkCartAsCheckoutStartedAction;
use App\Application\Commerce\Actions\Cart\MarkCartAsConvertedAction;
use App\Application\Commerce\Actions\Cart\RecalculateCartTotalsAction;
use App\Application\Commerce\Exceptions\AppliedCouponChangedException;
use App\Application\Promotion\Actions\Coupon\ReserveCouponForOrderAction;
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
        private readonly ReserveCouponForOrderAction        $reserveCouponForOrderAction,
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
            $cart = Cart::query()->whereKey($cart->id)->lockForUpdate()->firstOrFail();

            if (!$cart->isEditable()) {
                throw new DomainException('Only active carts can be converted to order.');
            }

            /*
             * Important:
             * Recalculate while cart is still ACTIVE.
             * Coupon validation depends on editable cart.
             */
            $previousCoupons = $cart->appliedCouponSnapshots();
            $previousCouponSignature = $cart->appliedCouponSignature();
            $previousTotalAmount = (int) $cart->total_amount;
            $previousDiscountAmount = (int) $cart->coupon_discount_amount;
            $cart = $this->recalculateCartTotalsAction->execute($cart);
            if ($previousCoupons !== [] && (
                $previousCouponSignature !== $cart->appliedCouponSignature()
                || $previousTotalAmount !== (int) $cart->total_amount
            )) {
                throw new AppliedCouponChangedException(
                    previousCouponCode: collect($previousCoupons)->pluck('code')->implode(' + '),
                    currentCouponCode: collect($cart->appliedCouponSnapshots())->pluck('code')->implode(' + '),
                    previousCouponDiscountAmount: $previousDiscountAmount,
                    currentCouponDiscountAmount: (int) $cart->coupon_discount_amount,
                    previousTotalAmount: $previousTotalAmount,
                    currentTotalAmount: (int) $cart->total_amount,
                );
            }

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
                'taxable_amount' => $cart->taxable_amount,
                'tax_rate_basis_points' => $cart->tax_rate_basis_points,
                'tax_amount' => $cart->tax_amount,
                'total_amount' => $cart->total_amount,

                'coupon_id' => $cart->coupon_id,
                'coupon_code' => $cart->coupon_code,
                'coupon_eligible_amount' => $cart->coupon_eligible_amount,
                'coupon_discount_amount' => $cart->coupon_discount_amount,
                'applied_coupons' => $cart->appliedCouponSnapshots(),

                'metadata' => $metadata,
            ]);

            $order->update([
                'order_number' => $this->generateOrderNumberAction->execute($order),
            ]);

            $this->reserveCouponForOrderAction->execute($order);

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

<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Promotion\Actions\Coupon\ApplyCouponDiscountToCartItemsAction;
use App\Application\Promotion\Actions\Coupon\CalculateCouponDiscountForCartAction;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use App\Domain\Promotion\Models\Coupon;
use DomainException;

class RecalculateCartTotalsAction
{
    public function __construct(
        private readonly RecalculateCartItemTotalsAction      $recalculateCartItemTotalsAction,
        private readonly CalculateCouponDiscountForCartAction $calculateCouponDiscountForCartAction,
        private readonly ApplyCouponDiscountToCartItemsAction $applyCouponDiscountToCartItemsAction,
    )
    {
    }

    public function execute(Cart $cart): Cart
    {
        $cart->loadMissing([
            'items.purchasable',
        ]);

        foreach ($cart->items as $item) {
            if (!$item instanceof CartItem) {
                continue;
            }

            $this->recalculateCartItemTotalsAction->execute($item);
        }

        $cart->load([
            'items.purchasable',
            'items.purchasable.product',
            'items.parent.purchasable',
            'items.parent.purchasable.product',
            'user',
        ]);

        $couponId = $cart->coupon_id;
        $couponCode = $cart->coupon_code;
        $couponEligibleAmount = 0;
        $couponDiscountAmount = 0;
        $couponAppliedAt = $cart->coupon_applied_at;

        if ($couponId || $couponCode) {
            $coupon = $this->resolveAppliedCoupon($cart);

            if ($coupon) {
                try {
                    $result = $this->calculateCouponDiscountForCartAction->execute(
                        cart: $cart,
                        coupon: $coupon,
                        user: $cart->user,
                    );

                    $this->applyCouponDiscountToCartItemsAction->execute(
                        cart: $cart,
                        itemDiscounts: $result->itemDiscounts,
                    );

                    $couponId = $coupon->id;
                    $couponCode = $coupon->code;
                    $couponEligibleAmount = $result->eligibleAmount;
                    $couponDiscountAmount = $result->discountAmount;
                    $couponAppliedAt = $couponAppliedAt ?: now();
                } catch (DomainException) {
                    $couponId = null;
                    $couponCode = null;
                    $couponEligibleAmount = 0;
                    $couponDiscountAmount = 0;
                    $couponAppliedAt = null;
                }
            } else {
                $couponId = null;
                $couponCode = null;
                $couponEligibleAmount = 0;
                $couponDiscountAmount = 0;
                $couponAppliedAt = null;
            }
        }

        $items = $cart->items()->get();

        $subtotalAmount = (int)$items->sum('subtotal_amount');
        $productDiscountAmount = (int)$items->sum('discount_amount');
        $allocatedCouponDiscountAmount = (int)$items->sum('coupon_discount_amount');

        $discountAmount = $productDiscountAmount + $allocatedCouponDiscountAmount;
        $totalAmount = (int)$items->sum('final_total_amount');

        $cart->forceFill([
            'subtotal_amount' => $subtotalAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => max(0, $totalAmount),

            'coupon_id' => $couponId,
            'coupon_code' => $couponCode,
            'coupon_eligible_amount' => $couponEligibleAmount,
            'coupon_discount_amount' => $couponDiscountAmount,
            'coupon_applied_at' => $couponAppliedAt,
        ])->save();

        return $cart->refresh();
    }

    private function resolveAppliedCoupon(Cart $cart): ?Coupon
    {
        if ($cart->coupon_id) {
            return Coupon::query()
                ->with([
                    'targets',
                    'couponUsers',
                ])
                ->find($cart->coupon_id);
        }

        if ($cart->coupon_code) {
            return Coupon::query()
                ->whereRaw('LOWER(code) = ?', [mb_strtolower($cart->coupon_code)])
                ->with([
                    'targets',
                    'couponUsers',
                ])
                ->first();
        }

        return null;
    }
}

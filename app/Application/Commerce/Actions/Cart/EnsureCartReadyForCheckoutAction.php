<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\Exceptions\AppliedCouponChangedException;
use App\Application\Commerce\Exceptions\CartNeedsRefreshException;
use App\Domain\Commerce\Models\Cart;
use DomainException;
use Throwable;

class EnsureCartReadyForCheckoutAction
{
    public function __construct(
        private readonly SyncCartItemsAction $syncCartItemsAction,
    )
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(Cart $cart): Cart
    {
        $cart = $cart->refresh();

        $previousCoupons = $cart->appliedCouponSnapshots();
        $previousCouponSignature = $cart->appliedCouponSignature();
        $hadCoupon = $previousCoupons !== [];

        $previousCouponCode = $hadCoupon ? collect($previousCoupons)->pluck('code')->implode(' + ') : null;
        $previousCouponDiscountAmount = (int)$cart->coupon_discount_amount;
        $previousTotalAmount = (int)$cart->total_amount;

        $result = $this->syncCartItemsAction->execute($cart);

        $cart = $result['cart']->refresh();

        if ((bool)($result['has_changes'] ?? false)) {
            throw new CartNeedsRefreshException($result);
        }

        $currentCoupons = $cart->appliedCouponSnapshots();
        if ($hadCoupon && $currentCoupons === []) {
            throw new DomainException('Applied coupon is no longer valid.');
        }

        $currentCouponCode = $currentCoupons !== [] ? collect($currentCoupons)->pluck('code')->implode(' + ') : null;

        $couponCodeChanged = $hadCoupon
            && (string)$previousCouponCode !== (string)$currentCouponCode;

        $couponAmountChanged = $hadCoupon
            && (
                $previousCouponSignature !== $cart->appliedCouponSignature()
                || $previousCouponDiscountAmount !== (int)$cart->coupon_discount_amount
                || $previousTotalAmount !== (int)$cart->total_amount
            );

        if ($couponCodeChanged || $couponAmountChanged) {
            throw new AppliedCouponChangedException(
                previousCouponCode: $previousCouponCode,
                currentCouponCode: $currentCouponCode,
                previousCouponDiscountAmount: $previousCouponDiscountAmount,
                currentCouponDiscountAmount: (int)$cart->coupon_discount_amount,
                previousTotalAmount: $previousTotalAmount,
                currentTotalAmount: (int)$cart->total_amount,
            );
        }

        if ($cart->items()->count() < 1) {
            throw new DomainException('Cart is empty.');
        }

        if (!$cart->isEditable()) {
            throw new DomainException('Cart is not editable.');
        }

        return $cart->refresh();
    }
}

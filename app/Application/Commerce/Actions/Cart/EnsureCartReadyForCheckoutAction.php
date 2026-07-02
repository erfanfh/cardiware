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

        $hadCoupon = filled($cart->coupon_id) || filled($cart->coupon_code);

        $previousCouponCode = $cart->coupon_code;
        $previousCouponDiscountAmount = (int)$cart->coupon_discount_amount;
        $previousTotalAmount = (int)$cart->total_amount;

        $result = $this->syncCartItemsAction->execute($cart);

        $cart = $result['cart']->refresh();

        if ((bool)($result['has_changes'] ?? false)) {
            throw new CartNeedsRefreshException($result);
        }

        if ($hadCoupon && blank($cart->coupon_id) && blank($cart->coupon_code)) {
            throw new DomainException('Applied coupon is no longer valid.');
        }

        if (
            $hadCoupon
            && (
                $previousCouponDiscountAmount !== (int)$cart->coupon_discount_amount
                || $previousTotalAmount !== (int)$cart->total_amount
            )
        ) {
            throw new AppliedCouponChangedException(
                couponCode: $cart->coupon_code ?: $previousCouponCode,
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

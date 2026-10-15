<?php

namespace App\Application\Commerce\Actions\Cart;

use App\Application\Commerce\Support\TaxCalculator;
use App\Application\Promotion\Actions\Coupon\RecalculateAppliedCartCouponsAction;
use App\Domain\Commerce\Models\Cart;
use App\Domain\Commerce\Models\CartItem;
use App\Domain\Identity\Models\User;

class RecalculateCartTotalsAction
{
    public function __construct(
        private readonly RecalculateCartItemTotalsAction $recalculateCartItemTotalsAction,
        private readonly RecalculateAppliedCartCouponsAction $recalculateAppliedCartCouponsAction,
        private readonly TaxCalculator $taxCalculator,
    ) {}

    public function execute(Cart $cart, ?int $requiredCouponId = null, ?User $user = null): Cart
    {
        $cart->loadMissing('items.purchasable');

        foreach ($cart->items as $item) {
            if ($item instanceof CartItem) {
                $this->recalculateCartItemTotalsAction->execute($item);
            }
        }

        $cart->load(['items.purchasable', 'items.parent.purchasable', 'user']);
        $coupons = $this->recalculateAppliedCartCouponsAction->execute($cart, $requiredCouponId, $user);
        $firstCoupon = $coupons[0] ?? null;
        $items = $cart->items()->get();
        $couponDiscountAmount = (int) $items->sum('coupon_discount_amount');
        $tax = $this->taxCalculator->calculate((int) $items->sum('final_total_amount'));

        $cart->forceFill([
            'subtotal_amount' => (int) $items->sum('subtotal_amount'),
            'discount_amount' => (int) $items->sum('discount_amount') + $couponDiscountAmount,
            'taxable_amount' => $tax->taxableAmount,
            'tax_rate_basis_points' => $tax->rateBasisPoints,
            'tax_amount' => $tax->taxAmount,
            'total_amount' => $tax->totalAmount,

            'applied_coupons' => $coupons,
            // Legacy fields retain the first code and the aggregate discount.
            'coupon_id' => $firstCoupon['coupon_id'] ?? null,
            'coupon_code' => $firstCoupon['code'] ?? null,
            'coupon_eligible_amount' => $firstCoupon['eligible_amount'] ?? 0,
            'coupon_discount_amount' => $couponDiscountAmount,
            'coupon_applied_at' => $firstCoupon['applied_at'] ?? null,
        ])->save();

        return $cart->refresh();
    }
}

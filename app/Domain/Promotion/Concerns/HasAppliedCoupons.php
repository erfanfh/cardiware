<?php

namespace App\Domain\Promotion\Concerns;

trait HasAppliedCoupons
{
    /** @return list<array{coupon_id: ?int, code: ?string, eligible_amount: int, discount_amount: int}> */
    public function appliedCouponSignature(): array
    {
        return array_map(fn (array $coupon): array => [
            'coupon_id' => $coupon['coupon_id'],
            'code' => $coupon['code'],
            'eligible_amount' => (int) $coupon['eligible_amount'],
            'discount_amount' => (int) $coupon['discount_amount'],
        ], $this->appliedCouponSnapshots());
    }

    /**
     * Coupon snapshots are authoritative once populated. Null preserves legacy carts/orders.
     *
     * @return list<array{coupon_id: ?int, code: ?string, eligible_amount: int, discount_amount: int, applied_at: ?string}>
     */
    public function appliedCouponSnapshots(): array
    {
        if ($this->applied_coupons !== null) {
            return $this->applied_coupons;
        }

        if (! $this->coupon_id && ! $this->coupon_code) {
            return [];
        }

        return [[
            'coupon_id' => $this->coupon_id ? (int) $this->coupon_id : null,
            'code' => $this->coupon_code,
            'eligible_amount' => (int) $this->coupon_eligible_amount,
            'discount_amount' => (int) $this->coupon_discount_amount,
            'applied_at' => $this->getAttribute('coupon_applied_at')?->toDateTimeString(),
        ]];
    }
}

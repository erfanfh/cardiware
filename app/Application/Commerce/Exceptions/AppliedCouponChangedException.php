<?php

namespace App\Application\Commerce\Exceptions;

use RuntimeException;

class AppliedCouponChangedException extends RuntimeException
{
    public function __construct(
        public readonly ?string $previousCouponCode,
        public readonly ?string $currentCouponCode,
        public readonly int     $previousCouponDiscountAmount,
        public readonly int     $currentCouponDiscountAmount,
        public readonly int     $previousTotalAmount,
        public readonly int     $currentTotalAmount,
    )
    {
        parent::__construct('Applied coupon changed.');
    }

    public function hasCodeChanged(): bool
    {
        return $this->previousCouponCode !== $this->currentCouponCode;
    }

    public function hasAmountChanged(): bool
    {
        return $this->previousCouponDiscountAmount !== $this->currentCouponDiscountAmount
            || $this->previousTotalAmount !== $this->currentTotalAmount;
    }
}

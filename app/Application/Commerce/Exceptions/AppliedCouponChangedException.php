<?php

namespace App\Application\Commerce\Exceptions;

use RuntimeException;

class AppliedCouponChangedException extends RuntimeException
{
    public function __construct(
        public readonly ?string $couponCode,
        public readonly int     $previousCouponDiscountAmount,
        public readonly int     $currentCouponDiscountAmount,
        public readonly int     $previousTotalAmount,
        public readonly int     $currentTotalAmount,
    )
    {
        parent::__construct('Applied coupon changed.');
    }
}

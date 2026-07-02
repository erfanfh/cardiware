<?php

namespace App\Application\Promotion\Exceptions;

use DomainException;

class CouponMinimumOrderAmountNotMetException extends DomainException
{
    public function __construct(
        public readonly int $minimumOrderAmount,
        public readonly int $currentAmount,
    )
    {
        parent::__construct('Coupon minimum order amount not met.');
    }
}

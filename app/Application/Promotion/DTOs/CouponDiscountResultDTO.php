<?php

namespace App\Application\Promotion\DTOs;

class CouponDiscountResultDTO
{
    public function __construct(
        public readonly int $eligibleAmount,
        public readonly int $discountAmount,
    )
    {
    }
}

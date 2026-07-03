<?php

namespace App\Application\Promotion\DTOs;

class CouponDiscountResultDTO
{
    /**
     * @param array<int, int> $itemDiscounts
     */
    public function __construct(
        public readonly int   $eligibleAmount,
        public readonly int   $discountAmount,
        public readonly array $itemDiscounts = [],
    )
    {
    }

    public function discountForItem(int $itemId): int
    {
        return (int)($this->itemDiscounts[$itemId] ?? 0);
    }
}

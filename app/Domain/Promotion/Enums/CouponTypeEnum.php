<?php

namespace App\Domain\Promotion\Enums;

enum CouponTypeEnum: string
{
    case PERCENTAGE = 'percentage';
    case FIXED_AMOUNT = 'fixed_amount';

    public function label(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'درصدی',
            self::FIXED_AMOUNT => 'مبلغ ثابت',
        };
    }

    public function isPercentage(): bool
    {
        return $this === self::PERCENTAGE;
    }

    public function isFixedAmount(): bool
    {
        return $this === self::FIXED_AMOUNT;
    }
}

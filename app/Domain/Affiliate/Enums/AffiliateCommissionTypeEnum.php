<?php

namespace App\Domain\Affiliate\Enums;

enum AffiliateCommissionTypeEnum: string
{
    case PERCENT = 'percent';
    case FIXED = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::PERCENT => 'درصدی از مبلغ سفارش',
            self::FIXED => 'مبلغ ثابت (تومان)',
        };
    }
}

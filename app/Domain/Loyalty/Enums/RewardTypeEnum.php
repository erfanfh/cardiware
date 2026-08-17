<?php

namespace App\Domain\Loyalty\Enums;

enum RewardTypeEnum: string
{
    case COUPON = 'coupon';

    public function label(): string
    {
        return match ($this) {
            self::COUPON => 'کد تخفیف',
        };
    }
}

<?php

namespace App\Domain\Loyalty\Enums;

enum RewardTypeEnum: string
{
    case COUPON = 'coupon';
    case RAFFLE_TICKET = 'raffle_ticket';

    public function label(): string
    {
        return match ($this) {
            self::COUPON => 'کد تخفیف',
            self::RAFFLE_TICKET => 'بلیط قرعه کشی',
        };
    }
}

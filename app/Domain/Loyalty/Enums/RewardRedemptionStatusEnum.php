<?php

namespace App\Domain\Loyalty\Enums;

enum RewardRedemptionStatusEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case REVERSED = 'reversed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'در حال پردازش',
            self::COMPLETED => 'تکمیل شده',
            self::REVERSED => 'برگشت داده شده',
        };
    }
}

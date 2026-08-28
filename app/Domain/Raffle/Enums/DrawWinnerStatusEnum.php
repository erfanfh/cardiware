<?php

namespace App\Domain\Raffle\Enums;

enum DrawWinnerStatusEnum: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case REJECTED = 'rejected';
    case EXPIRED = 'expired';
    case DELIVERED = 'delivered';
    case REPLACED = 'replaced';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'در انتظار تایید',
            self::CONFIRMED => 'تایید شده',
            self::REJECTED => 'رد شده',
            self::EXPIRED => 'منقضی شده',
            self::DELIVERED => 'جایزه تحویل شده',
            self::REPLACED => 'جایگزین شده',
        };
    }
}

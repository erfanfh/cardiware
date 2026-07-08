<?php

namespace App\Domain\Promotion\Enums;

enum CouponReservationStatusEnum: string
{
    case ACTIVE = 'active';
    case REDEEMED = 'redeemed';
    case RELEASED = 'released';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'فعال',
            self::REDEEMED => 'مصرف شده',
            self::RELEASED => 'آزاد شده',
            self::EXPIRED => 'منقضی شده',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isRedeemed(): bool
    {
        return $this === self::REDEEMED;
    }
}

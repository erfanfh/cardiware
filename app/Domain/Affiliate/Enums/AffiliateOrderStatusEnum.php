<?php

namespace App\Domain\Affiliate\Enums;

enum AffiliateOrderStatusEnum: string
{
    case ACTIVE = 'active';
    case REVERTED = 'reverted';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'فعال',
            self::REVERTED => 'برگشت خورده (رفاند)',
        };
    }
}

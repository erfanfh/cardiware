<?php

namespace App\Domain\Commerce\Enums;

enum PurchaseAccessStatusEnum: string
{
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case REVOKED = 'revoked';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'فعال',
            self::EXPIRED => 'منقضی شده',
            self::REVOKED => 'لغو شده',
        };
    }

    /**
     * Determine whether the access is usable.
     */
    public function isUsable(): bool
    {
        return $this === self::ACTIVE;
    }
}

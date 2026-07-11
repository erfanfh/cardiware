<?php

namespace App\Domain\Financial\Enums;

enum WalletStatusEnum: string
{
    case ACTIVE = 'active';
    case BLOCKED = 'blocked';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'فعال',
            self::BLOCKED => 'مسدود',
        };
    }

    /**
     * Determine whether the wallet can be used by its owner.
     */
    public function allowsUserTransactions(): bool
    {
        return $this === self::ACTIVE;
    }
}

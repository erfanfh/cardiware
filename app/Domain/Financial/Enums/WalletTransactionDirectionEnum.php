<?php

namespace App\Domain\Financial\Enums;

enum WalletTransactionDirectionEnum: string
{
    case CREDIT = 'credit';
    case DEBIT = 'debit';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::CREDIT => 'افزایش موجودی',
            self::DEBIT => 'کاهش موجودی',
        };
    }

    /**
     * Determine whether the transaction increases the balance.
     */
    public function isCredit(): bool
    {
        return $this === self::CREDIT;
    }

    /**
     * Determine whether the transaction decreases the balance.
     */
    public function isDebit(): bool
    {
        return $this === self::DEBIT;
    }
}

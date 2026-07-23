<?php

namespace App\Domain\Financial\Enums;

enum FinancialActivityDirectionEnum: string
{
    case CREDIT = 'credit';

    case DEBIT = 'debit';

    public function label(): string
    {
        return match ($this) {
            self::CREDIT => 'واریز',
            self::DEBIT => 'برداشت',
        };
    }

    public function isCredit(): bool
    {
        return $this === self::CREDIT;
    }

    public function isDebit(): bool
    {
        return $this === self::DEBIT;
    }

    public function sign(): string
    {
        return match ($this) {
            self::CREDIT => '+',
            self::DEBIT => '-',
        };
    }
}

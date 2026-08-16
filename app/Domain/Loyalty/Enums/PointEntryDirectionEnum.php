<?php

namespace App\Domain\Loyalty\Enums;

enum PointEntryDirectionEnum: string
{
    case CREDIT = 'credit';
    case DEBIT = 'debit';

    public function label(): string
    {
        return match ($this) {
            self::CREDIT => 'افزایش امتیاز',
            self::DEBIT => 'کاهش امتیاز',
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

    public function opposite(): self
    {
        return $this === self::CREDIT
            ? self::DEBIT
            : self::CREDIT;
    }
}

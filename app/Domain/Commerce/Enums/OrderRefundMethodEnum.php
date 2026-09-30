<?php

namespace App\Domain\Commerce\Enums;

enum OrderRefundMethodEnum: string
{
    case WALLET = 'wallet';
    case MANUAL_BANK_TRANSFER = 'manual_bank_transfer';

    public function label(): string
    {
        return match ($this) {
            self::WALLET => 'بازگشت به کیف پول',
            self::MANUAL_BANK_TRANSFER => 'واریز دستی به حساب کاربر',
        };
    }
}

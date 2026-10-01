<?php

namespace App\Domain\Financial\Enums;

enum WalletWithdrawalStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case PAID = 'paid';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'در انتظار بررسی',
            self::APPROVED => 'تأیید شده، در انتظار واریز',
            self::PAID => 'واریز شده',
            self::REJECTED => 'رد شده',
        };
    }
}

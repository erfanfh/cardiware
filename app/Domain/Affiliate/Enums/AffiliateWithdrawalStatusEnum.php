<?php

namespace App\Domain\Affiliate\Enums;

enum AffiliateWithdrawalStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'در انتظار بررسی',
            self::APPROVED => 'تأیید و پرداخت شده',
            self::REJECTED => 'رد شده',
        };
    }
}

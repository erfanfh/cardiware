<?php

namespace App\Domain\Marketplace\Enums;

enum StoreWithdrawalStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'در انتظار بررسی',
            self::APPROVED => 'تأییدشده',
            self::REJECTED => 'ردشده',
        };
    }
}

<?php

namespace App\Domain\Financial\Enums;

enum FinancialActivityStatusEnum: string
{
    case PENDING = 'pending';

    case SUCCESSFUL = 'successful';

    case FAILED = 'failed';

    case CANCELLED = 'cancelled';

    case EXPIRED = 'expired';

    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'در انتظار',
            self::SUCCESSFUL => 'موفق',
            self::FAILED => 'ناموفق',
            self::CANCELLED => 'لغو شده',
            self::EXPIRED => 'منقضی شده',
            self::REFUNDED => 'مسترد شده',
        };
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isSuccessful(): bool
    {
        return $this === self::SUCCESSFUL;
    }

    public function isFailed(): bool
    {
        return in_array($this, [
            self::FAILED,
            self::CANCELLED,
            self::EXPIRED,
        ], true);
    }

    public function hasCompletedFinancialEffect(): bool
    {
        return in_array($this, [
            self::SUCCESSFUL,
            self::REFUNDED,
        ], true);
    }
}

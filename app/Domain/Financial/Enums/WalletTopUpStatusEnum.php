<?php

namespace App\Domain\Financial\Enums;

enum WalletTopUpStatusEnum: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'در انتظار پرداخت',
            self::COMPLETED => 'تکمیل شده',
            self::CANCELLED => 'لغو شده',
            self::EXPIRED => 'منقضی شده',
        };
    }

    public function canStartPayment(): bool
    {
        return $this === self::PENDING_PAYMENT;
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::COMPLETED,
            self::CANCELLED,
            self::EXPIRED,
        ], true);
    }
}

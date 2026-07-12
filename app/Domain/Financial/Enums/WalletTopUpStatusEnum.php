<?php

namespace App\Domain\Financial\Enums;

enum WalletTopUpStatusEnum: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'در انتظار پرداخت',
            self::COMPLETED => 'تکمیل شده',
            self::FAILED => 'ناموفق',
            self::CANCELLED => 'لغو شده',
            self::EXPIRED => 'منقضی شده',
        };
    }

    public function canStartPayment(): bool
    {
        return $this === self::PENDING_PAYMENT;
    }

    public function isPendingPayment(): bool
    {
        return $this === self::PENDING_PAYMENT;
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this === self::FAILED;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    public function isExpired(): bool
    {
        return $this === self::EXPIRED;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::COMPLETED,
            self::FAILED,
            self::CANCELLED,
            self::EXPIRED,
        ], true);
    }

    public function canTransitionTo(self $status): bool
    {
        if ($this === $status) {
            return true;
        }

        return match ($this) {
            self::PENDING_PAYMENT => in_array($status, [
                self::COMPLETED,
                self::FAILED,
                self::CANCELLED,
                self::EXPIRED,
            ], true),

            self::COMPLETED,
            self::FAILED,
            self::CANCELLED,
            self::EXPIRED => false,
        };
    }
}

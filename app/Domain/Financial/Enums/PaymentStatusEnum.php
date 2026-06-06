<?php

namespace App\Domain\Financial\Enums;

enum PaymentStatusEnum: string
{
    case INITIATED = 'initiated';
    case PENDING = 'pending';
    case PAID = 'paid';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::INITIATED => 'ایجاد شده',
            self::PENDING => 'در انتظار تایید',
            self::PAID => 'پرداخت شده',
            self::FAILED => 'ناموفق',
            self::CANCELLED => 'لغو شده',
            self::EXPIRED => 'منقضی شده',
        };
    }

    /**
     * Determine whether the payment is successful.
     */
    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    /**
     * Determine whether the payment has failed.
     */
    public function isFailed(): bool
    {
        return in_array($this, [
            self::FAILED,
            self::CANCELLED,
            self::EXPIRED,
        ], true);
    }

    /**
     * Determine whether the payment can be verified.
     */
    public function canBeVerified(): bool
    {
        return in_array($this, [
            self::INITIATED,
            self::PENDING,
        ], true);
    }
}

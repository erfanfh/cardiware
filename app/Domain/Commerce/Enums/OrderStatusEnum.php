<?php

namespace App\Domain\Commerce\Enums;

enum OrderStatusEnum: string
{
    case PENDING_PAYMENT = 'pending_payment';
    case PAID = 'paid';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING_PAYMENT => 'در انتظار پرداخت',
            self::PAID => 'پرداخت شده',
            self::FAILED => 'ناموفق',
            self::CANCELLED => 'لغو شده',
            self::REFUNDED => 'مسترد شده',
        };
    }

    /**
     * Determine whether the order is paid.
     */
    public function isPaid(): bool
    {
        return $this === self::PAID;
    }

    /**
     * Determine whether the order can start payment.
     */
    public function canStartPayment(): bool
    {
        return $this === self::PENDING_PAYMENT;
    }
}

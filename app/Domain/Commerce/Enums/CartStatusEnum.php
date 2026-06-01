<?php

namespace App\Domain\Commerce\Enums;

enum CartStatusEnum: string
{
    case ACTIVE = 'active';
    case CHECKOUT_STARTED = 'checkout_started';
    case CONVERTED = 'converted';
    case ABANDONED = 'abandoned';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'فعال',
            self::CHECKOUT_STARTED => 'در حال پرداخت',
            self::CONVERTED => 'تبدیل شده به سفارش',
            self::ABANDONED => 'رها شده',
        };
    }

    /**
     * Determine whether the cart can be changed.
     */
    public function isEditable(): bool
    {
        return $this === self::ACTIVE;
    }

    /**
     * Determine whether the cart is already locked.
     */
    public function isLocked(): bool
    {
        return in_array($this, [
            self::CHECKOUT_STARTED,
            self::CONVERTED,
        ], true);
    }
}

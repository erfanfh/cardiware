<?php

namespace App\Domain\Financial\Enums;

enum WalletTopUpPurposeEnum: string
{
    case MANUAL = 'manual';
    case ORDER_PAYMENT = 'order_payment';

    public function label(): string
    {
        return match ($this) {
            self::MANUAL => 'شارژ عادی کیف پول',
            self::ORDER_PAYMENT => 'تکمیل موجودی برای پرداخت سفارش',
        };
    }

    public function isManual(): bool
    {
        return $this === self::MANUAL;
    }

    public function isOrderPayment(): bool
    {
        return $this === self::ORDER_PAYMENT;
    }
}

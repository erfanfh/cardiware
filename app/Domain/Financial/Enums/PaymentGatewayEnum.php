<?php

namespace App\Domain\Financial\Enums;

enum PaymentGatewayEnum: string
{
    case ZIBAL = 'zibal';
    case ZARINPAL = 'zarinpal';
    case PAYPING = 'payping';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ZIBAL => 'زیبال',
            self::ZARINPAL => 'زرین پال',
            self::PAYPING => 'پی پینگ',
        };
    }
}

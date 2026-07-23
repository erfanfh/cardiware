<?php

namespace App\Domain\Financial\Enums;

enum PaymentGatewayEnum: string
{
    case ZIBAL = 'zibal';
    case ZARINPAL = 'zarinpal';
    case PAYPING = 'payping';
    case AQAYEPARDAKHT = 'aqayepardakht';

    case WALLET = 'wallet';
    case FREE = 'free';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ZIBAL => 'زیبال',
            self::ZARINPAL => 'زرین پال',
            self::PAYPING => 'پی پینگ',
            self::AQAYEPARDAKHT => 'آقای پرداخت',
            self::WALLET => 'کیف پول',
            self::FREE => 'پرداخت رایگان',
        };
    }

    /**
     * Determine whether this gateway requires an external request.
     */
    public function isExternal(): bool
    {
        return in_array($this, [
            self::ZIBAL,
            self::ZARINPAL,
            self::PAYPING,
            self::AQAYEPARDAKHT,
        ], true);
    }

    /**
     * Determine whether this is an internal wallet payment.
     */
    public function isWallet(): bool
    {
        return $this === self::WALLET;
    }
}

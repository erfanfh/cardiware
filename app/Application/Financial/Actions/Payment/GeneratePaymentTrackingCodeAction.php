<?php

namespace App\Application\Financial\Actions\Payment;

use App\Domain\Financial\Models\Payment;
use Random\RandomException;

class GeneratePaymentTrackingCodeAction
{
    /**
     * Generate a unique internal payment tracking code.
     *
     * @return string
     * @throws RandomException
     */
    public function execute(): string
    {
        do {
            $trackingCode = 'PAY-' . now()->format('Ymd') . '-' . str_pad(
                    string: (string)random_int(1, 999999),
                    length: 6,
                    pad_string: '0',
                    pad_type: STR_PAD_LEFT,
                );
        } while (
            Payment::query()
                ->where('tracking_code', $trackingCode)
                ->exists()
        );

        return $trackingCode;
    }
}

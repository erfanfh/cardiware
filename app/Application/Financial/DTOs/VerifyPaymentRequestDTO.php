<?php

namespace App\Application\Financial\DTOs;

use App\Domain\Financial\Models\Payment;

readonly class VerifyPaymentRequestDTO
{
    /**
     * @param array<string, mixed> $callbackPayload
     */
    public function __construct(
        public Payment $payment,
        public array   $callbackPayload = [],
    )
    {
    }
}

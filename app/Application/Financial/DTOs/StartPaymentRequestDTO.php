<?php

namespace App\Application\Financial\DTOs;

use App\Domain\Financial\Models\Payment;

readonly class StartPaymentRequestDTO
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        public Payment $payment,
        public string  $callbackUrl,
        public array   $metadata = [],
    )
    {
    }
}

<?php

namespace App\Application\Financial\DTOs;

readonly class VerifyPaymentResultDTO
{
    /**
     * @param array<string, mixed> $rawResponse
     */
    public function __construct(
        public bool    $successful,
        public ?string $gatewayTransactionId = null,
        public ?string $gatewayStatus = null,
        public ?string $gatewayMessage = null,
        public array   $rawResponse = [],
    )
    {
    }
}

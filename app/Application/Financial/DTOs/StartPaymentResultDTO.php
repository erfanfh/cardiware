<?php

namespace App\Application\Financial\DTOs;

readonly class StartPaymentResultDTO
{
    /**
     * @param array<string, mixed> $rawResponse
     */
    public function __construct(
        public bool    $successful,
        public ?string $gatewayReference = null,
        public ?string $redirectUrl = null,
        public ?string $gatewayStatus = null,
        public ?string $gatewayMessage = null,
        public array   $rawResponse = [],
    )
    {
    }
}

<?php

namespace App\Application\Financial\DTOs;

final readonly class VerifyPaymentResultDTO
{
    /**
     * @param array<string, mixed> $rawResponse
     */
    public function __construct(
        public bool    $successful,
        public ?string $gatewayTransactionId,
        public string  $gatewayStatus,
        public ?string $gatewayMessage,
        public array   $rawResponse = [],

        /*
         * true:
         * The result is final and the payment may become PAID or FAILED.
         *
         * false:
         * The gateway result is currently indeterminate and the local
         * payment must remain PENDING.
         */
        public bool    $terminal = true,
    )
    {
    }

    public function shouldRemainPending(): bool
    {
        return !$this->successful && !$this->terminal;
    }
}

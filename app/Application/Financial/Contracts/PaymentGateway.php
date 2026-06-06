<?php

namespace App\Application\Financial\Contracts;

use App\Application\Financial\DTOs\StartPaymentRequestDTO;
use App\Application\Financial\DTOs\StartPaymentResultDTO;
use App\Application\Financial\DTOs\VerifyPaymentRequestDTO;
use App\Application\Financial\DTOs\VerifyPaymentResultDTO;
use App\Domain\Financial\Enums\PaymentGatewayEnum;

interface PaymentGateway
{
    /**
     * Get the gateway identifier.
     */
    public function gateway(): PaymentGatewayEnum;

    /**
     * Start a payment request.
     */
    public function start(StartPaymentRequestDTO $request): StartPaymentResultDTO;

    /**
     * Verify a payment request.
     */
    public function verify(VerifyPaymentRequestDTO $request): VerifyPaymentResultDTO;
}

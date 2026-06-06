<?php

namespace App\Application\Financial\Support;

use App\Application\Financial\Contracts\PaymentGateway;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use InvalidArgumentException;

class PaymentGatewayManager
{
    /**
     * @param iterable<PaymentGateway> $gateways
     */
    public function __construct(
        private readonly iterable $gateways,
    )
    {
    }

    /**
     * Get gateway implementation by enum.
     */
    public function get(PaymentGatewayEnum $gateway): PaymentGateway
    {
        foreach ($this->gateways as $paymentGateway) {
            if ($paymentGateway->gateway() === $gateway) {
                return $paymentGateway;
            }
        }

        throw new InvalidArgumentException("Payment gateway [{$gateway->value}] is not registered.");
    }
}

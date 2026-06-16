<?php

namespace App\Application\Financial\Actions\Payment;

use App\Domain\Financial\Enums\PaymentGatewayEnum;
use DomainException;

class ResolveDefaultPaymentGatewayAction
{
    /**
     * Resolve default payment gateway from config.
     */
    public function execute(): PaymentGatewayEnum
    {
        $gateway = get_setting('general', 'default_payment_gateway');

        $gatewayEnum = PaymentGatewayEnum::tryFrom((string)$gateway);

        if (!$gatewayEnum instanceof PaymentGatewayEnum) {
            throw new DomainException("Invalid default payment gateway [{$gateway}].");
        }

        return $gatewayEnum;
    }
}

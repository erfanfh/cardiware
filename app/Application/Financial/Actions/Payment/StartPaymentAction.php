<?php

namespace App\Application\Financial\Actions\Payment;

use App\Application\Financial\Contracts\Payable;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Models\Payment;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Throwable;

class StartPaymentAction
{
    public function __construct(
        private readonly CreatePaymentForPayableAction $createPaymentForPayableAction,
        private readonly StartCreatedPaymentAction     $startCreatedPaymentAction,
    )
    {
    }

    /**
     * @param Payable&Model $payable
     * @param PaymentGatewayEnum $gateway
     * @param string $callbackUrl
     * @param array<string, mixed> $metadata
     * @return Payment
     * @throws Throwable
     */
    public function execute(
        Payable&Model      $payable,
        PaymentGatewayEnum $gateway,
        string             $callbackUrl,
        array              $metadata = [],
    ): Payment
    {
        if (!$gateway->isExternal()) {
            throw new DomainException(
                "Gateway [{$gateway->value}] cannot start an external payment."
            );
        }

        $payment = $this->createPaymentForPayableAction->execute(
            payable: $payable,
            gateway: $gateway,
            requestPayload: [
                'callback_url' => $callbackUrl,
                'metadata' => $metadata,
            ],
        );

        return $this->startCreatedPaymentAction->execute(
            payment: $payment,
            callbackUrl: $callbackUrl,
            metadata: $metadata,
        );
    }
}

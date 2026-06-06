<?php

namespace App\Application\Financial\Actions\Payment;

use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Models\Payment;
use DomainException;

class MarkPaymentAsPendingAction
{
    /**
     * Mark payment as pending.
     *
     * @param array<string, mixed> $requestPayload
     */
    public function execute(
        Payment $payment,
        ?string $gatewayReference = null,
        ?string $gatewayStatus = null,
        ?string $gatewayMessage = null,
        array   $requestPayload = [],
    ): Payment
    {
        if ($payment->status !== PaymentStatusEnum::INITIATED) {
            throw new DomainException('Only initiated payments can be marked as pending.');
        }

        $payment->forceFill([
            'status' => PaymentStatusEnum::PENDING,
            'gateway_reference' => $gatewayReference,
            'gateway_status' => $gatewayStatus,
            'gateway_message' => $gatewayMessage,
            'request_payload' => array_merge($payment->request_payload ?? [], $requestPayload),
        ])->save();

        return $payment->refresh();
    }
}

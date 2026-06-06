<?php

namespace App\Application\Financial\Actions\Payment;

use App\Application\Financial\Events\PaymentFailed;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Models\Payment;

class MarkPaymentAsFailedAction
{
    /**
     * Mark payment as failed.
     *
     * @param array<string, mixed> $payload
     */
    public function execute(
        Payment $payment,
        ?string $gatewayStatus = null,
        ?string $gatewayMessage = null,
        array   $payload = [],
    ): Payment
    {
        if ($payment->isPaid()) {
            return $payment;
        }

        $payment->forceFill([
            'status' => PaymentStatusEnum::FAILED,
            'gateway_status' => $gatewayStatus,
            'gateway_message' => $gatewayMessage,
            'verify_payload' => array_merge($payment->verify_payload ?? [], $payload),
            'failed_at' => now(),
        ])->save();

        $payment = $payment->refresh();

        event(new PaymentFailed($payment));

        return $payment;
    }
}

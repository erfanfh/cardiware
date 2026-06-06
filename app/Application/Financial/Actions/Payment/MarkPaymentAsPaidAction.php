<?php

namespace App\Application\Financial\Actions\Payment;

use App\Application\Financial\Events\PaymentPaid;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Models\Payment;
use DomainException;

class MarkPaymentAsPaidAction
{
    /**
     * Mark payment as paid.
     *
     * @param array<string, mixed> $verifyPayload
     */
    public function execute(
        Payment $payment,
        ?string $gatewayTransactionId = null,
        ?string $gatewayStatus = null,
        ?string $gatewayMessage = null,
        array   $verifyPayload = [],
    ): Payment
    {
        if (!$payment->canBeVerified()) {
            throw new DomainException('Payment cannot be marked as paid.');
        }

        $payment->forceFill([
            'status' => PaymentStatusEnum::PAID,
            'gateway_transaction_id' => $gatewayTransactionId,
            'gateway_status' => $gatewayStatus,
            'gateway_message' => $gatewayMessage,
            'verify_payload' => $verifyPayload,
            'paid_at' => now(),
            'failed_at' => null,
        ])->save();

        $payment = $payment->refresh();

        event(new PaymentPaid($payment));

        return $payment;
    }
}

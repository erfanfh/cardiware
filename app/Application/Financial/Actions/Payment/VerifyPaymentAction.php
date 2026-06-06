<?php

namespace App\Application\Financial\Actions\Payment;

use App\Application\Financial\DTOs\VerifyPaymentRequestDTO;
use App\Application\Financial\Support\PaymentGatewayManager;
use App\Domain\Financial\Models\Payment;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class VerifyPaymentAction
{
    public function __construct(
        private readonly MarkPaymentAsPaidAction   $markPaymentAsPaidAction,
        private readonly MarkPaymentAsFailedAction $markPaymentAsFailedAction,
        private readonly PaymentGatewayManager     $paymentGatewayManager,
    )
    {
    }

    /**
     * Verify a payment through its gateway.
     *
     * @param array<string, mixed> $callbackPayload
     * @throws Throwable
     */
    public function execute(Payment $payment, array $callbackPayload = []): Payment
    {
        return DB::transaction(function () use ($payment, $callbackPayload) {
            $payment = $payment->refresh();

            if (!$payment->canBeVerified()) {
                throw new DomainException('Payment cannot be verified.');
            }

            $payment->forceFill([
                'callback_payload' => $callbackPayload,
            ])->save();

            $gatewayDriver = $this->paymentGatewayManager->get($payment->gateway);

            $result = $gatewayDriver->verify(
                new VerifyPaymentRequestDTO(
                    payment: $payment,
                    callbackPayload: $callbackPayload,
                ),
            );

            if (!$result->successful) {
                return $this->markPaymentAsFailedAction->execute(
                    payment: $payment,
                    gatewayStatus: $result->gatewayStatus,
                    gatewayMessage: $result->gatewayMessage,
                    payload: $result->rawResponse,
                );
            }

            return $this->markPaymentAsPaidAction->execute(
                payment: $payment,
                gatewayTransactionId: $result->gatewayTransactionId,
                gatewayStatus: $result->gatewayStatus,
                gatewayMessage: $result->gatewayMessage,
                verifyPayload: $result->rawResponse,
            );
        });
    }
}

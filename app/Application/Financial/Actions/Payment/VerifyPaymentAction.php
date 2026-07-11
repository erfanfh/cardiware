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
    public function execute(
        Payment $payment,
        array   $callbackPayload = [],
    ): Payment
    {
        /*
         * First, persist the callback and validate the current state
         * in a short transaction.
         */
        $payment = DB::transaction(function () use (
            $payment,
            $callbackPayload,
        ): Payment {
            $payment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->isPaid()) {
                return $payment;
            }

            if (!$payment->canBeVerified()) {
                throw new DomainException('Payment cannot be verified.');
            }

            if (!$payment->gateway->isExternal()) {
                throw new DomainException(
                    "Gateway [{$payment->gateway->value}] cannot be externally verified."
                );
            }

            $payment->forceFill([
                'callback_payload' => $callbackPayload,
            ])->save();

            return $payment->refresh();
        });

        if ($payment->isPaid()) {
            return $payment;
        }

        /*
         * Important:
         * The gateway HTTP request runs outside the database transaction.
         */
        $gatewayDriver = $this->paymentGatewayManager->get($payment->gateway);

        $result = $gatewayDriver->verify(
            new VerifyPaymentRequestDTO(
                payment: $payment,
                callbackPayload: $callbackPayload,
            ),
        );

        /*
         * Apply the verification result atomically.
         */
        return DB::transaction(function () use (
            $payment,
            $result,
        ): Payment {
            $payment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * Another callback may already have completed the payment.
             */
            if ($payment->isPaid()) {
                return $payment;
            }

            /*
             * Prevent an old concurrent verification response from
             * overwriting a newer terminal payment state.
             */
            if (!$payment->canBeVerified()) {
                return $payment;
            }

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

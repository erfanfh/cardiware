<?php

namespace App\Application\Financial\Actions\Payment;

use App\Application\Financial\DTOs\VerifyPaymentRequestDTO;
use App\Application\Financial\DTOs\VerifyPaymentResultDTO;
use App\Application\Financial\Support\PaymentGatewayManager;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Models\Payment;
use DomainException;
use Illuminate\Support\Facades\Cache;
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
         * Browser callback and Zibal server callback may arrive
         * at exactly the same time.
         *
         * This lock serializes the complete external verification flow.
         */
        return Cache::lock(
            name: "financial:payment:verify:{$payment->getKey()}",
            seconds: 60,
        )->block(
            seconds: 25,
            callback: fn(): Payment => $this->executeWithinLock(
                payment: $payment,
                callbackPayload: $callbackPayload,
            ),
        );
    }

    /**
     * @param array<string, mixed> $callbackPayload
     *
     * @throws Throwable
     */
    private function executeWithinLock(
        Payment $payment,
        array   $callbackPayload,
    ): Payment
    {
        /*
         * Reload state after acquiring the distributed lock.
         *
         * Another callback may already have completed the payment
         * while this request was waiting for the lock.
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
         * External HTTP call runs outside the database transaction
         * but remains protected by the payment-specific cache lock.
         */
        $gatewayDriver = $this->paymentGatewayManager->get($payment->gateway);

        $result = $gatewayDriver->verify(
            new VerifyPaymentRequestDTO(
                payment: $payment,
                callbackPayload: $callbackPayload,
            ),
        );

        return DB::transaction(function () use (
            $payment,
            $result,
        ): Payment {
            $payment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($payment->isPaid()) {
                return $payment;
            }

            if (!$payment->canBeVerified()) {
                return $payment;
            }

            if ($result->successful) {
                return $this->markPaymentAsPaidAction->execute(
                    payment: $payment,
                    gatewayTransactionId: $result->gatewayTransactionId,
                    gatewayStatus: $result->gatewayStatus,
                    gatewayMessage: $result->gatewayMessage,
                    verifyPayload: $result->rawResponse,
                );
            }

            /*
             * Timeout, connection failure, HTTP errors, unknown gateway
             * result and paid-unverified transactions stay PENDING.
             */
            if ($result->shouldRemainPending()) {
                return $this->keepPaymentPending(
                    payment: $payment,
                    result: $result,
                );
            }

            /*
             * Only definitive gateway failures reach this branch.
             */
            return $this->markPaymentAsFailedAction->execute(
                payment: $payment,
                gatewayStatus: $result->gatewayStatus,
                gatewayMessage: $result->gatewayMessage,
                payload: $result->rawResponse,
            );
        });
    }

    private function keepPaymentPending(
        Payment                $payment,
        VerifyPaymentResultDTO $result,
    ): Payment
    {
        $payment->forceFill([
            'status' => PaymentStatusEnum::PENDING,
            'gateway_status' => $result->gatewayStatus,
            'gateway_message' => $result->gatewayMessage,
            'verify_payload' => $result->rawResponse,
            'failed_at' => null,
        ])->save();

        return $payment->refresh();
    }
}

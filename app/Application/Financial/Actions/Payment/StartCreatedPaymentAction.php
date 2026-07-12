<?php

namespace App\Application\Financial\Actions\Payment;

use App\Application\Financial\DTOs\StartPaymentRequestDTO;
use App\Application\Financial\Support\PaymentGatewayManager;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Models\Payment;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class StartCreatedPaymentAction
{
    public function __construct(
        private readonly MarkPaymentAsPendingAction $markPaymentAsPendingAction,
        private readonly MarkPaymentAsFailedAction  $markPaymentAsFailedAction,
        private readonly PaymentGatewayManager      $paymentGatewayManager,
    )
    {
    }

    /**
     * @param array<string, mixed> $metadata
     * @throws Throwable
     */
    public function execute(
        Payment $payment,
        string  $callbackUrl,
        array   $metadata = [],
    ): Payment
    {
        $payment->refresh();

        /*
         * Idempotent retry after gateway request was already completed.
         */
        if ($payment->status !== PaymentStatusEnum::INITIATED) {
            return $payment;
        }

        if (!$payment->gateway->isExternal()) {
            throw new DomainException(
                'Only external payments can be started through a gateway.'
            );
        }

        /*
         * External HTTP request is outside a database transaction.
         */
        $gatewayDriver = $this->paymentGatewayManager->get(
            $payment->gateway,
        );

        $result = $gatewayDriver->start(
            new StartPaymentRequestDTO(
                payment: $payment,
                callbackUrl: $callbackUrl,
                metadata: $metadata,
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

            if ($payment->status !== PaymentStatusEnum::INITIATED) {
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

            return $this->markPaymentAsPendingAction->execute(
                payment: $payment,
                gatewayReference: $result->gatewayReference,
                gatewayStatus: $result->gatewayStatus,
                gatewayMessage: $result->gatewayMessage,
                requestPayload: [
                    'redirect_url' => $result->redirectUrl,
                    'gateway_response' => $result->rawResponse,
                ],
            );
        }, 3);
    }
}

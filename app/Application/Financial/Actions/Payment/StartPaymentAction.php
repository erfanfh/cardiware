<?php

namespace App\Application\Financial\Actions\Payment;

use App\Application\Financial\Contracts\Payable;
use App\Application\Financial\DTOs\StartPaymentRequestDTO;
use App\Application\Financial\Support\PaymentGatewayManager;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Models\Payment;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class StartPaymentAction
{
    public function __construct(
        private readonly CreatePaymentForPayableAction $createPaymentForPayableAction,
        private readonly MarkPaymentAsPendingAction    $markPaymentAsPendingAction,
        private readonly MarkPaymentAsFailedAction     $markPaymentAsFailedAction,
        private readonly PaymentGatewayManager         $paymentGatewayManager,
    )
    {
    }

    /**
     * Start a payment for a payable model.
     *
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
                "Gateway [{$gateway->value}] cannot be started as an external payment."
            );
        }

        /*
         * Create the payment in a short database transaction.
         */
        $payment = $this->createPaymentForPayableAction->execute(
            payable: $payable,
            gateway: $gateway,
            requestPayload: [
                'callback_url' => $callbackUrl,
                'metadata' => $metadata,
            ],
        );

        /*
         * Important:
         * External HTTP request must not run inside a database transaction.
         */
        $gatewayDriver = $this->paymentGatewayManager->get($gateway);

        $result = $gatewayDriver->start(
            new StartPaymentRequestDTO(
                payment: $payment,
                callbackUrl: $callbackUrl,
                metadata: $metadata,
            ),
        );

        /*
         * Persist the gateway response in another short transaction.
         */
        return DB::transaction(function () use ($payment, $result): Payment {
            $payment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * Another process may already have changed the payment.
             */
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
        });
    }
}

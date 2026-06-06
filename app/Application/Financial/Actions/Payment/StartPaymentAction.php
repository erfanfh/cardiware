<?php

namespace App\Application\Financial\Actions\Payment;

use App\Application\Financial\Contracts\Payable;
use App\Application\Financial\DTOs\StartPaymentRequestDTO;
use App\Application\Financial\Support\PaymentGatewayManager;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Models\Payment;
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
        return DB::transaction(function () use ($payable, $gateway, $callbackUrl, $metadata) {
            $payment = $this->createPaymentForPayableAction->execute(
                payable: $payable,
                gateway: $gateway,
                requestPayload: [
                    'callback_url' => $callbackUrl,
                    'metadata' => $metadata,
                ],
            );

            $gatewayDriver = $this->paymentGatewayManager->get($gateway);

            $result = $gatewayDriver->start(
                new StartPaymentRequestDTO(
                    payment: $payment,
                    callbackUrl: $callbackUrl,
                    metadata: $metadata,
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

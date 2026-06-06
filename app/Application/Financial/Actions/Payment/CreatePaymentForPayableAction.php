<?php

namespace App\Application\Financial\Actions\Payment;

use App\Application\Financial\Contracts\Payable;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Models\Payment;
use DomainException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreatePaymentForPayableAction
{
    public function __construct(
        private readonly GeneratePaymentTrackingCodeAction $generatePaymentTrackingCodeAction,
    )
    {
    }

    /**
     * Create a payment record for a payable model.
     *
     * @param Payable&Model $payable
     * @param PaymentGatewayEnum $gateway
     * @param array<string, mixed> $requestPayload
     * @return Payment
     * @throws Throwable
     */
    public function execute(
        Payable&Model      $payable,
        PaymentGatewayEnum $gateway,
        array              $requestPayload = [],
    ): Payment
    {
        return DB::transaction(function () use ($payable, $gateway, $requestPayload) {
            if (!$payable->canStartPayment()) {
                throw new DomainException('Payable cannot start payment.');
            }

            $amount = $payable->getPayableAmount();

            if ($amount <= 0) {
                throw new DomainException('Payment amount must be greater than zero.');
            }

            return Payment::query()->create([
                'payable_type' => $payable->getMorphClass(),
                'payable_id' => $payable->getKey(),
                'gateway' => $gateway,
                'status' => PaymentStatusEnum::INITIATED,
                'amount' => $amount,
                'tracking_code' => $this->generatePaymentTrackingCodeAction->execute(),
                'request_payload' => $requestPayload,
            ]);
        });
    }
}

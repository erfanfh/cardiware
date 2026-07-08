<?php

namespace App\Application\Financial\Actions\Payment;

use App\Application\Promotion\Actions\Coupon\ReleaseCouponReservationForOrderAction;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Models\Payment;
use Throwable;

class MarkPaymentAsFailedAction
{
    /**
     * Mark payment as failed.
     *
     * @param array<string, mixed> $payload
     * @throws Throwable
     */
    public function execute(
        Payment $payment,
        ?string $gatewayStatus = null,
        ?string $gatewayMessage = null,
        array   $payload = [],
    ): Payment
    {
        if ($payment->isPaid()) {
            return $payment->refresh();
        }

        $payment->forceFill([
            'status' => PaymentStatusEnum::FAILED,
            'gateway_status' => $gatewayStatus,
            'gateway_message' => $gatewayMessage,
            'verify_payload' => array_merge($payment->verify_payload ?? [], $payload),
            'failed_at' => now(),
        ])->save();

        $payment = $payment->refresh();

        $payable = $payment->payable;

        if ($payable instanceof Order) {
            app(ReleaseCouponReservationForOrderAction::class)->execute(
                order: $payable,
                reason: 'payment_failed',
            );
        }

        return $payment;
    }
}

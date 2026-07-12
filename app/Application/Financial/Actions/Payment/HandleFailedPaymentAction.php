<?php

namespace App\Application\Financial\Actions\Payment;

use App\Application\Commerce\Actions\Checkout\FailOrderWalletShortfallPaymentAction;
use App\Application\Financial\Actions\Wallet\FailManualWalletTopUpPaymentAction;
use App\Application\Promotion\Actions\Coupon\ReleaseCouponReservationForOrderAction;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Models\Payment;
use App\Domain\Financial\Models\WalletTopUp;
use DomainException;
use Illuminate\Database\Eloquent\Model;

class HandleFailedPaymentAction
{
    public function __construct(
        private readonly ReleaseCouponReservationForOrderAction $releaseCouponReservationForOrderAction,
        private readonly FailManualWalletTopUpPaymentAction     $failManualWalletTopUpPaymentAction,
        private readonly FailOrderWalletShortfallPaymentAction  $failOrderWalletShortfallPaymentAction,
    )
    {
    }

    public function execute(Payment $payment): Model
    {
        $payment = Payment::query()
            ->with('payable')
            ->findOrFail($payment->id);

        if ($payment->isPaid()) {
            throw new DomainException(
                'Paid payment cannot be handled as failed.'
            );
        }

        if (!$this->isUnsuccessfulTerminal($payment)) {
            /*
             * Pending and initiated payments are not finalized.
             */
            return $payment;
        }

        $payable = $payment->payable;

        return match (true) {
            $payable instanceof Order => $this->handleFailedOrderPayment(
                payment: $payment,
                order: $payable,
            ),

            $payable instanceof WalletTopUp && $payable->isManual() => $this->failManualWalletTopUpPaymentAction
                ->execute(
                    payment: $payment,
                    reason: $payment->gateway_message,
                ),

            $payable instanceof WalletTopUp && $payable->isOrderPayment() => $this->failOrderWalletShortfallPaymentAction
                ->execute(
                    payment: $payment,
                    reason: $payment->gateway_message,
                ),

            default => $payment,
        };
    }

    private function handleFailedOrderPayment(
        Payment $payment,
        Order   $order,
    ): Order
    {
        $this->releaseCouponReservationForOrderAction->execute(
            order: $order,
            reason: $payment->gateway_message ?: 'external_payment_failed',
        );

        return $order->refresh();
    }

    private function isUnsuccessfulTerminal(Payment $payment): bool
    {
        return in_array($payment->status, [
            PaymentStatusEnum::FAILED,
            PaymentStatusEnum::CANCELLED,
            PaymentStatusEnum::EXPIRED,
        ], true);
    }
}

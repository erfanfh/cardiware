<?php

namespace App\Application\Commerce\Actions\Checkout;

use App\Application\Financial\Exceptions\WalletIntegrityException;
use App\Application\Financial\Support\WalletBalanceCalculator;
use App\Application\Promotion\Actions\Coupon\ReleaseCouponReservationForOrderAction;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Enums\WalletHoldStatusEnum;
use App\Domain\Financial\Enums\WalletTopUpStatusEnum;
use App\Domain\Financial\Models\Payment;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Financial\Models\WalletHold;
use App\Domain\Financial\Models\WalletTopUp;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class FailOrderWalletShortfallPaymentAction
{
    public function __construct(
        private readonly WalletBalanceCalculator                $walletBalanceCalculator,
        private readonly ReleaseCouponReservationForOrderAction $releaseCouponReservationForOrderAction,
    )
    {
    }

    /**
     * Release the wallet contribution after the external
     * shortage payment fails, expires or is cancelled.
     *
     * @throws Throwable
     */
    public function execute(Payment $payment, ?string $reason = null): WalletTopUp
    {
        $seedPayment = Payment::query()
            ->with('payable')
            ->findOrFail($payment->id);

        $seedTopUp = $seedPayment->payable;

        if (!$seedTopUp instanceof WalletTopUp || !$seedTopUp->isOrderPayment()) {
            throw new DomainException(
                'Payment does not belong to an order wallet shortfall top-up.'
            );
        }

        $orderId = (int)$seedTopUp->order_id;
        $paymentId = (int)$seedPayment->id;
        $walletId = (int)$seedTopUp->wallet_id;
        $walletHoldId = (int)$seedTopUp->wallet_hold_id;
        $topUpId = (int)$seedTopUp->id;

        $topUp = DB::transaction(function () use (
            $orderId,
            $paymentId,
            $walletId,
            $walletHoldId,
            $topUpId,
            $reason,
        ): WalletTopUp {
            /*
             * Keep the same lock order as the successful completion:
             *
             * 1. Order
             * 2. External Payment
             * 3. Wallet
             * 4. WalletHold
             * 5. WalletTopUp
             */

            /** @var Order $order */
            $order = Order::query()
                ->whereKey($orderId)
                ->lockForUpdate()
                ->firstOrFail();

            /** @var Payment $payment */
            $payment = Payment::query()
                ->whereKey($paymentId)
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * A paid callback may have won the race.
             * Never release its hold through the failure path.
             */
            if ($payment->isPaid()) {
                return WalletTopUp::query()->findOrFail($topUpId);
            }

            if (!$this->isUnsuccessfulTerminal($payment)) {
                throw new DomainException(
                    'Payment has not reached an unsuccessful terminal status.'
                );
            }

            /** @var Wallet $wallet */
            $wallet = Wallet::query()
                ->whereKey($walletId)
                ->lockForUpdate()
                ->firstOrFail();

            /** @var WalletHold $walletHold */
            $walletHold = WalletHold::query()
                ->whereKey($walletHoldId)
                ->lockForUpdate()
                ->firstOrFail();

            /** @var WalletTopUp $topUp */
            $topUp = WalletTopUp::query()
                ->whereKey($topUpId)
                ->lockForUpdate()
                ->firstOrFail();

            $this->assertIntegrity(
                payment: $payment,
                topUp: $topUp,
                order: $order,
                wallet: $wallet,
                walletHold: $walletHold,
            );

            if ($topUp->isCompleted()) {
                throw new WalletIntegrityException(
                    'Completed order wallet top-up cannot be marked as failed.'
                );
            }

            $this->walletBalanceCalculator->assertIntegrity($wallet);

            /*
             * Always resolve an active hold before returning,
             * even when the top-up was already marked as failed.
             */
            if ($walletHold->status === WalletHoldStatusEnum::ACTIVE) {
                if ((int)$wallet->held_balance < (int)$walletHold->amount) {
                    throw new WalletIntegrityException(
                        'Wallet held balance is lower than wallet hold amount.'
                    );
                }

                if ((int)$wallet->held_withdrawable_balance < (int)$walletHold->withdrawable_amount) {
                    throw new WalletIntegrityException(
                        'Wallet held withdrawable balance is lower than wallet hold amount.'
                    );
                }

                $wallet->forceFill([
                    'held_balance' => (int)$wallet->held_balance - (int)$walletHold->amount,
                    'held_withdrawable_balance' => (int)$wallet->held_withdrawable_balance - (int)$walletHold->withdrawable_amount,
                ])->save();

                $walletHold->forceFill([
                    'status' => WalletHoldStatusEnum::RELEASED,
                    'released_by_user_id' => null,
                    'release_reason' => $reason ?: $payment->gateway_message ?: 'order_wallet_shortfall_payment_failed',
                    'released_at' => now(),
                ])->save();
            } elseif ($walletHold->status === WalletHoldStatusEnum::CAPTURED) {
                throw new WalletIntegrityException(
                    'Captured wallet hold cannot belong to a failed payment.'
                );
            }

            /*
             * RELEASED and EXPIRED holds have already returned
             * their reserved balance and require no additional change.
             */
            $this->walletBalanceCalculator->assertIntegrity($wallet);

            if (!$topUp->status->isTerminal()) {
                $topUp->forceFill([
                    'status' => WalletTopUpStatusEnum::FAILED,
                    'failed_at' => now(),
                    'failure_reason' => $reason ?: $payment->gateway_message ?: 'External order shortage payment failed.',
                ])->save();
            }

            return $topUp->refresh()->load([
                'wallet',
                'order',
                'walletHold',
                'payment',
            ]);
        }, 3);

        $latestPayment = $topUp->payment()->first();

        if ($latestPayment && $this->isUnsuccessfulTerminal($latestPayment) && $topUp->order) {
            $this->releaseCouponReservationForOrderAction->execute(
                order: $topUp->order,
                reason: $reason ?: $latestPayment->gateway_message ?: 'order_wallet_shortfall_payment_failed',
            );
        }

        return $topUp;
    }

    private function assertIntegrity(
        Payment     $payment,
        WalletTopUp $topUp,
        Order       $order,
        Wallet      $wallet,
        WalletHold  $walletHold,
    ): void
    {
        if (!$topUp->isOrderPayment()) {
            throw new WalletIntegrityException(
                'Wallet top-up purpose is not order payment.'
            );
        }

        if ((int)$topUp->payment_id !== (int)$payment->id) {
            throw new WalletIntegrityException(
                'Wallet top-up payment does not match.'
            );
        }

        if (
            $payment->payable_type !== $topUp->getMorphClass()
            || (int)$payment->payable_id !== (int)$topUp->id
        ) {
            throw new WalletIntegrityException(
                'Payment payable does not match wallet top-up.'
            );
        }

        if ((int)$topUp->order_id !== (int)$order->id) {
            throw new WalletIntegrityException(
                'Wallet top-up order does not match.'
            );
        }

        if ((int)$topUp->wallet_id !== (int)$wallet->id) {
            throw new WalletIntegrityException(
                'Wallet top-up wallet does not match.'
            );
        }

        if ((int)$topUp->wallet_hold_id !== (int)$walletHold->id) {
            throw new WalletIntegrityException(
                'Wallet top-up hold does not match.'
            );
        }

        if ((int)$walletHold->wallet_id !== (int)$wallet->id) {
            throw new WalletIntegrityException(
                'Wallet hold does not belong to wallet.'
            );
        }

        if (
            $walletHold->reference_type !== $order->getMorphClass()
            ||
            (int)$walletHold->reference_id !== (int)$order->id
        ) {
            throw new WalletIntegrityException(
                'Wallet hold reference does not match order.'
            );
        }

        if ((int)$walletHold->amount + (int)$topUp->amount !== (int)$order->getPayableAmount()) {
            throw new WalletIntegrityException(
                'Wallet hold and top-up amounts do not equal order total.'
            );
        }
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

<?php

namespace App\Application\Commerce\Actions\Checkout;

use App\Application\Financial\Actions\Wallet\CreditWalletAction;
use App\Application\Financial\Actions\Wallet\ReleaseWalletHoldAction;
use App\Application\Financial\DTOs\CreditWalletData;
use App\Application\Financial\Exceptions\WalletIntegrityException;
use App\Application\Shared\Cache\Invalidators\UserCacheInvalidator;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Enums\WalletHoldStatusEnum;
use App\Domain\Financial\Enums\WalletHoldTypeEnum;
use App\Domain\Financial\Enums\WalletTopUpStatusEnum;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Financial\Models\Payment;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Financial\Models\WalletHold;
use App\Domain\Financial\Models\WalletTopUp;
use App\Domain\Identity\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class CompletePaidOrderWalletTopUpAction
{
    public function __construct(
        private readonly CreditWalletAction       $creditWalletAction,
        private readonly ReleaseWalletHoldAction  $releaseWalletHoldAction,
        private readonly PayOrderWithWalletAction $payOrderWithWalletAction,
    )
    {
    }

    /**
     * Credit the paid shortage and complete the related order.
     *
     * @throws Throwable
     */
    public function execute(Payment $payment): WalletTopUp
    {
        $seedPayment = Payment::query()
            ->with('payable')
            ->findOrFail($payment->id);

        if (!$seedPayment->isPaid()) {
            throw new DomainException(
                'Only paid payments can complete order wallet top-up.'
            );
        }

        $seedTopUp = $seedPayment->payable;

        if (!$seedTopUp instanceof WalletTopUp || !$seedTopUp->isOrderPayment()) {
            throw new DomainException(
                'Payment does not belong to an order wallet top-up.'
            );
        }

        $topUpId = (int)$seedTopUp->id;
        $orderId = (int)$seedTopUp->order_id;
        $walletId = (int)$seedTopUp->wallet_id;
        $walletHoldId = (int)$seedTopUp->wallet_hold_id;
        $paymentId = (int)$seedPayment->id;

        $topUp = DB::transaction(function () use (
            $topUpId,
            $orderId,
            $walletId,
            $walletHoldId,
            $paymentId,
        ): WalletTopUp {
            /*
             * Consistent locking order:
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

            /** @var Payment $externalPayment */
            $externalPayment = Payment::query()
                ->whereKey($paymentId)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$externalPayment->isPaid()) {
                throw new DomainException(
                    'External shortfall payment is not paid.'
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

            /*
             * Idempotent callback retry.
             */
            if ($topUp->isCompleted()) {
                if (!$topUp->credited_transaction_id) {
                    throw new WalletIntegrityException(
                        'Completed order wallet top-up has no credit transaction.'
                    );
                }

                if ($topUp->order_payment_id !== null && !$order->isPaid()) {
                    throw new WalletIntegrityException(
                        'Top-up has an order payment but order is not paid.'
                    );
                }

                return $topUp->load([
                    'wallet',
                    'order',
                    'walletHold',
                    'payment',
                    'creditedTransaction',
                    'orderPayment',
                ]);
            }

            if ($topUp->status !== WalletTopUpStatusEnum::PENDING_PAYMENT) {
                throw new WalletIntegrityException(
                    'Paid order wallet top-up is not pending payment.'
                );
            }

            $user = User::query()
                ->whereKey($order->user_id)
                ->firstOrFail();

            $this->assertIntegrity(
                topUp: $topUp,
                externalPayment: $externalPayment,
                order: $order,
                wallet: $wallet,
                walletHold: $walletHold,
                user: $user,
            );

            /*
             * Credit only the shortage paid through the gateway.
             *
             * This amount is deliberately non-withdrawable.
             */
            $creditTransaction = $this->creditWalletAction->execute(
                new CreditWalletData(
                    wallet: $wallet,
                    type: WalletTransactionTypeEnum::TOP_UP,
                    amount: (int)$topUp->amount,
                    idempotencyKey: 'order-wallet-shortfall-credit:' . $topUp->public_token,
                    withdrawableAmount: 0,
                    actor: $user,
                    reference: $topUp,
                    description: 'تکمیل موجودی برای پرداخت سفارش ' . $order->order_number,
                    metadata: [
                        'purpose' => $topUp->purpose->value,
                        'external_payment_id' => $externalPayment->id,
                        'external_payment_token' => $externalPayment->public_token,
                        'gateway' => $externalPayment->gateway->value,
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'wallet_hold_id' => $walletHold->id,
                        'wallet_hold_token' => $walletHold->public_token,
                    ],
                ),
            );

            /*
             * The previous wallet contribution was reserved while
             * the user was at the gateway. It must now become available
             * before debiting the entire order amount.
             */
            if ($walletHold->status === WalletHoldStatusEnum::ACTIVE) {
                $walletHold = $this->releaseWalletHoldAction->execute(
                    hold: $walletHold,
                    releasedBy: null,
                    reason: 'order_wallet_shortfall_payment_succeeded',
                );
            } elseif ($walletHold->status === WalletHoldStatusEnum::CAPTURED) {
                throw new WalletIntegrityException(
                    'Order wallet shortfall hold has already been captured.'
                );
            }

            $wallet->refresh();

            /*
             * There are rare race/recovery situations where another
             * payment may already have completed the order.
             *
             * The paid shortage must still be credited to the wallet,
             * but it must not pay the order twice.
             */
            if ($order->isPaid()) {
                $topUp->forceFill([
                    'status' => WalletTopUpStatusEnum::COMPLETED,
                    'credited_transaction_id' => $creditTransaction->id,
                    'order_payment_id' => null,
                    'completed_at' => now(),
                    'failed_at' => null,
                    'failure_reason' => null,
                    'metadata' => array_merge(
                        $topUp->metadata ?? [],
                        [
                            'completion_mode' =>
                                'credited_only_order_already_paid',
                        ],
                    ),
                ])->save();

                return $topUp->refresh()->load([
                    'wallet',
                    'order',
                    'walletHold',
                    'payment',
                    'creditedTransaction',
                ]);
            }

            /*
             * Normally:
             *
             * available wallet balance
             * = old held contribution + paid shortage
             * = full order amount
             */
            if ((int)$wallet->available_balance < (int)$order->getPayableAmount()) {
                throw new WalletIntegrityException(
                    'Wallet balance is insufficient after paid shortfall credit.'
                );
            }

            /*
             * This creates:
             *
             * - internal WALLET Payment
             * - ORDER_PAYMENT wallet transaction
             * - PAID order
             * - purchase accesses
             */
            $orderPayment = $this->payOrderWithWalletAction->execute(
                order: $order,
                user: $user,
            );

            $topUp->forceFill([
                'status' => WalletTopUpStatusEnum::COMPLETED,
                'credited_transaction_id' => $creditTransaction->id,
                'order_payment_id' => $orderPayment->id,
                'completed_at' => now(),
                'failed_at' => null,
                'failure_reason' => null,

                'metadata' => array_merge(
                    $topUp->metadata ?? [],
                    [
                        'completion_mode' => 'order_paid_with_wallet',
                        'order_payment_id' => $orderPayment->id,
                        'order_payment_token' => $orderPayment->public_token,
                    ],
                ),
            ])->save();

            return $topUp->refresh()->load([
                'wallet',
                'order',
                'walletHold',
                'payment',
                'creditedTransaction',
                'orderPayment',
            ]);
        }, 3);

        DB::afterCommit(function () use ($topUp): void {
            $user = $topUp->wallet?->user;

            if ($user) {
                app(UserCacheInvalidator::class)->flushForUser($user);
            }
        });

        return $topUp;
    }

    private function assertIntegrity(
        WalletTopUp $topUp,
        Payment     $externalPayment,
        Order       $order,
        Wallet      $wallet,
        WalletHold  $walletHold,
        User        $user,
    ): void
    {
        if (!$topUp->isOrderPayment()) {
            throw new WalletIntegrityException(
                'Wallet top-up purpose is not order payment.'
            );
        }

        if ((int)$topUp->payment_id !== (int)$externalPayment->id) {
            throw new WalletIntegrityException(
                'Wallet top-up external payment does not match.'
            );
        }

        if ((int)$externalPayment->amount !== (int)$topUp->amount) {
            throw new WalletIntegrityException(
                'Wallet top-up amount does not match external payment amount.'
            );
        }

        if (
            $externalPayment->payable_type
            !== $topUp->getMorphClass()
            || (int)$externalPayment->payable_id
            !== (int)$topUp->id
        ) {
            throw new WalletIntegrityException(
                'External payment payable does not match wallet top-up.'
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

        if ($walletHold->type !== WalletHoldTypeEnum::ORDER_PAYMENT) {
            throw new WalletIntegrityException(
                'Wallet hold type is not order payment.'
            );
        }

        if (
            $walletHold->reference_type
            !== $order->getMorphClass()
            || (int)$walletHold->reference_id
            !== (int)$order->id
        ) {
            throw new WalletIntegrityException(
                'Wallet hold reference does not match order.'
            );
        }

        if ((int)$order->user_id !== (int)$user->id) {
            throw new WalletIntegrityException(
                'Order owner does not match expected user.'
            );
        }

        if ((int)$wallet->user_id !== (int)$user->id) {
            throw new WalletIntegrityException(
                'Wallet owner does not match order owner.'
            );
        }

        if ((int)$topUp->withdrawable_amount !== 0) {
            throw new WalletIntegrityException(
                'Order wallet top-up cannot be withdrawable.'
            );
        }

        if ((int)$walletHold->amount + (int)$topUp->amount !== (int)$order->getPayableAmount()) {
            throw new WalletIntegrityException(
                'Wallet hold and top-up amounts do not equal order total.'
            );
        }
    }
}

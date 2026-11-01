<?php

namespace App\Application\Commerce\Actions\Checkout;

use App\Application\Financial\Actions\Payment\CreatePaymentForPayableAction;
use App\Application\Financial\Actions\Payment\MarkPaymentAsPaidAction;
use App\Application\Financial\Actions\Wallet\DebitWalletAction;
use App\Application\Financial\Actions\Wallet\ResolveWalletOrderPaymentCapAction;
use App\Application\Financial\Support\WalletBalanceCalculator;
use App\Application\Financial\Actions\Wallet\GetOrCreateWalletAction;
use App\Application\Financial\DTOs\DebitWalletDTO;
use App\Application\Financial\Exceptions\WalletIntegrityException;
use App\Application\Shared\Cache\Invalidators\OrderCacheInvalidator;
use App\Application\Shared\Cache\Invalidators\UserCacheInvalidator;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Enums\WalletDebitPolicyEnum;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Financial\Models\Payment;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Identity\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class PayOrderWithWalletAction
{
    public function __construct(
        private readonly GetOrCreateWalletAction                   $getOrCreateWalletAction,
        private readonly CreatePaymentForPayableAction             $createPaymentForPayableAction,
        private readonly DebitWalletAction                         $debitWalletAction,
        private readonly MarkPaymentAsPaidAction                   $markPaymentAsPaidAction,
        private readonly CompleteOrderAfterSuccessfulPaymentAction $completeOrderAfterSuccessfulPaymentAction,
        private readonly ResolveWalletOrderPaymentCapAction        $resolveWalletOrderPaymentCapAction,
        private readonly WalletBalanceCalculator                   $walletBalanceCalculator,
    )
    {
    }

    /**
     * Pay the entire order amount using wallet balance.
     *
     * @throws Throwable
     */
    public function execute(
        Order $order,
        User  $user,
    ): Payment
    {
        $this->validateModels(
            order: $order,
            user: $user,
        );

        /*
         * Create the wallet before entering the main transaction.
         * Concurrent creation is already protected by user_id unique index.
         */
        $wallet = $this->getOrCreateWalletAction->execute($user);

        $payment = DB::transaction(function () use (
            $order,
            $user,
            $wallet,
        ): Payment {
            /*
             * Lock order first.
             *
             * This prevents two concurrent payment methods from completing
             * the same order at the same time.
             */
            /** @var Order $order */
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int)$order->user_id !== (int)$user->id) {
                throw new DomainException(
                    'The order does not belong to this user.'
                );
            }

            /*
             * Idempotent duplicate submission:
             *
             * If another request already completed the order, return its
             * successful payment instead of debiting the wallet again.
             */
            if ($order->isPaid()) {
                $existingPayment = $order->payments()
                    ->where('status', PaymentStatusEnum::PAID->value)
                    ->latest('id')
                    ->first();

                if (!$existingPayment) {
                    throw new WalletIntegrityException(
                        'Paid order has no successful payment.'
                    );
                }

                return $existingPayment;
            }

            if (!$order->canStartPayment()) {
                throw new DomainException(
                    'Order cannot be paid in its current status.'
                );
            }

            $amount = (int)$order->getPayableAmount();

            if ($amount <= 0) {
                throw new DomainException(
                    'Free orders must be completed through the free payment flow.'
                );
            }

            /*
             * Lock wallet after the order.
             */
            /** @var Wallet $wallet */
            $wallet = Wallet::query()
                ->whereKey($wallet->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int)$wallet->user_id !== (int)$user->id) {
                throw new WalletIntegrityException(
                    'Wallet owner does not match order owner.'
                );
            }

            /*
             * سقف استفاده از اعتبار باشگاه در هر سفارش.
             */
            $maxWalletAmount = $this->resolveWalletOrderPaymentCapAction->execute(
                $amount,
                $wallet,
            );

            $canPayFullAmount = $this->walletBalanceCalculator->availableBalance($wallet) >= $amount;

            if ($canPayFullAmount && $amount > $maxWalletAmount) {
                throw new DomainException(
                    'سهم اعتبار باشگاه از پرداخت این سفارش از حد مجاز بیشتر است؛ مابقی را از درگاه پرداخت کنید.'
                );
            }

            /*
             * Create the internal payment record.
             */
            $payment = $this->createPaymentForPayableAction->execute(
                payable: $order,
                gateway: PaymentGatewayEnum::WALLET,
                requestPayload: [
                    'type' => 'wallet_order_payment',
                    'user_id' => $user->id,
                    'wallet_id' => $wallet->id,
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
            );

            /*
             * DebitWalletAction:
             *
             * - checks wallet status
             * - checks available balance
             * - respects held balance
             * - consumes non-withdrawable credit first
             * - creates an immutable ledger transaction
             */
            $walletTransaction = $this->debitWalletAction->execute(
                new DebitWalletDTO(
                    wallet: $wallet,
                    type: WalletTransactionTypeEnum::ORDER_PAYMENT,
                    amount: $amount,
                    idempotencyKey: 'order-wallet-payment:' . $order->id,
                    policy: WalletDebitPolicyEnum::NON_WITHDRAWABLE_FIRST,
                    actor: $user,
                    reference: $order,
                    description: 'پرداخت سفارش ' . $order->order_number . ' با کیف پول',
                    metadata: [
                        'payment_id' => $payment->id,
                        'payment_token' => $payment->public_token,
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'tax_amount' => (int)$order->tax_amount,
                    ],
                    taxAmount: (int)$order->tax_amount,
                ),
            );

            /*
             * Mark the internal payment as paid.
             */
            $payment = $this->markPaymentAsPaidAction->execute(
                payment: $payment,
                gatewayTransactionId: $walletTransaction->public_token,
                gatewayStatus: 'wallet_paid',
                gatewayMessage: 'مبلغ سفارش با موفقیت از کیف پول کسر شد.',
                verifyPayload: [
                    'type' => 'wallet_order_payment',
                    'verified_without_external_gateway' => true,

                    'wallet_id' => $wallet->id,

                    'wallet_transaction_id' => $walletTransaction->id,

                    'wallet_transaction_token' => $walletTransaction->public_token,

                    'order_id' => $order->id,
                    'order_number' => $order->order_number,

                    'amount' => $amount,
                ],
            );

            /*
             * Complete all commerce side effects inside the same transaction:
             *
             * - mark order as paid
             * - consume coupon reservation
             * - grant purchase accesses
             * - run other successful checkout actions
             */
            $this->completeOrderAfterSuccessfulPaymentAction->execute($order);

            return $payment->refresh();
        }, 3);

        /*
         * Cache invalidation must happen after the database transaction
         * has been committed successfully.
         */
        DB::afterCommit(function () use ($user): void {
            app(UserCacheInvalidator::class)->flushForUser($user);
            app(OrderCacheInvalidator::class)->flushSuccessfulPayment();
        });

        return $payment->loadMissing('payable');
    }

    private function validateModels(
        Order $order,
        User  $user,
    ): void
    {
        if (!$order->exists) {
            throw new InvalidArgumentException(
                'Order must exist before wallet payment.'
            );
        }

        if (!$user->exists) {
            throw new InvalidArgumentException(
                'Wallet payment user must exist.'
            );
        }
    }
}

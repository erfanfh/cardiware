<?php

namespace App\Application\Commerce\Actions\Checkout;

use App\Application\Financial\Actions\Payment\CreatePaymentForPayableAction;
use App\Application\Financial\Actions\Payment\StartCreatedPaymentAction;
use App\Application\Financial\Actions\Wallet\CreateWalletHoldAction;
use App\Application\Financial\Actions\Wallet\GetOrCreateWalletAction;
use App\Application\Financial\Actions\Wallet\ResolveWalletOrderPaymentCapAction;
use App\Application\Financial\DTOs\CreateWalletHoldDTO;
use App\Application\Financial\Exceptions\WalletIntegrityException;
use App\Application\Financial\Exceptions\WalletTopUpIdempotencyConflictException;
use App\Application\Financial\Exceptions\WalletUnavailableException;
use App\Application\Financial\Support\WalletBalanceCalculator;
use App\Domain\Commerce\Models\Order;
use App\Domain\Financial\Enums\PaymentGatewayEnum;
use App\Domain\Financial\Enums\PaymentStatusEnum;
use App\Domain\Financial\Enums\WalletHoldTypeEnum;
use App\Domain\Financial\Enums\WalletTopUpPurposeEnum;
use App\Domain\Financial\Enums\WalletTopUpStatusEnum;
use App\Domain\Financial\Models\Payment;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Financial\Models\WalletTopUp;
use App\Domain\Identity\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class StartOrderWalletShortfallPaymentAction
{
    public function __construct(
        private readonly GetOrCreateWalletAction               $getOrCreateWalletAction,
        private readonly WalletBalanceCalculator               $walletBalanceCalculator,
        private readonly CreateWalletHoldAction                $createWalletHoldAction,
        private readonly CreatePaymentForPayableAction         $createPaymentForPayableAction,
        private readonly StartCreatedPaymentAction             $startCreatedPaymentAction,
        private readonly FailOrderWalletShortfallPaymentAction $failShortfallPaymentAction,
        private readonly ResolveWalletOrderPaymentCapAction   $resolveWalletOrderPaymentCapAction,
    )
    {
    }

    public function execute(
        Order              $order,
        User               $user,
        PaymentGatewayEnum $gateway,
        string             $requestKey,
        string             $callbackUrl,
        array              $metadata = [],
    ): Payment
    {
        $payment = $this->prepare(
            order: $order,
            user: $user,
            gateway: $gateway,
            requestKey: $requestKey,
            callbackUrl: $callbackUrl,
            metadata: $metadata,
        );

        return $this->startPreparedPayment(
            payment: $payment,
            callbackUrl: $callbackUrl,
            metadata: $metadata,
        );
    }

    /**
     * Reserve current wallet balance and start an external payment
     * only for the remaining order amount.
     *
     * @param array<string, mixed> $metadata
     *
     * @throws Throwable
     */
    public function prepare(
        Order              $order,
        User               $user,
        PaymentGatewayEnum $gateway,
        string             $requestKey,
        string             $callbackUrl,
        array              $metadata = [],
    ): Payment
    {
        $this->validate(
            order: $order,
            user: $user,
            gateway: $gateway,
            requestKey: $requestKey,
            callbackUrl: $callbackUrl,
        );

        $existingPayment = $this->findExistingPayment(
            requestKey: $requestKey,
            order: $order,
            user: $user,
            gateway: $gateway,
        );

        if ($existingPayment) {
            return $existingPayment;
        }

        $wallet = $this->getOrCreateWalletAction->execute($user);

        return DB::transaction(function () use (
            $order,
            $user,
            $wallet,
            $gateway,
            $requestKey,
            $callbackUrl,
            $metadata,
        ): Payment {
            /*
             * Order is locked before wallet to prevent two payment
             * methods from starting for the same order concurrently.
             */
            /** @var Order $order */
            $order = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            $existingPayment = $this->findExistingPayment(
                requestKey: $requestKey,
                order: $order,
                user: $user,
                gateway: $gateway,
            );

            if ($existingPayment) {
                return $existingPayment;
            }

            if ((int)$order->user_id !== (int)$user->id) {
                throw new DomainException(
                    'The order does not belong to this user.'
                );
            }

            if ($order->isPaid()) {
                throw new DomainException(
                    'Order has already been paid.'
                );
            }

            if (!$order->canStartPayment()) {
                throw new DomainException(
                    'Order cannot be paid in its current status.'
                );
            }

            $orderTotal = (int)$order->getPayableAmount();

            if ($orderTotal <= 0) {
                throw new DomainException(
                    'Free orders cannot use wallet shortfall payment.'
                );
            }

            /*
             * Prevent another active mixed payment from reserving
             * the wallet again for the same order.
             */
            $activeTopUp = WalletTopUp::query()
                ->where('order_id', $order->id)
                ->where(
                    'purpose',
                    WalletTopUpPurposeEnum::ORDER_PAYMENT->value,
                )
                ->where(
                    'status',
                    WalletTopUpStatusEnum::PENDING_PAYMENT->value,
                )
                ->where('request_key', '!=', $requestKey)
                ->where(function ($query): void {
                    $query
                        ->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                })
                ->latest('id')
                ->first();

            if ($activeTopUp) {
                throw new DomainException(
                    'Another wallet shortfall payment is already in progress for this order.'
                );
            }

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

            if (!$wallet->allowsUserTransactions()) {
                throw new WalletUnavailableException(
                    'Wallet is not available for order payment.'
                );
            }

            $this->walletBalanceCalculator->assertIntegrity($wallet);

            $walletAmount = min(
                $this->walletBalanceCalculator->availableBalance($wallet),
                $this->resolveWalletOrderPaymentCapAction->execute($orderTotal, $wallet),
            );

            /*
             * This Action is only for genuinely mixed payments.
             */
            if ($walletAmount <= 0) {
                throw new DomainException(
                    'Wallet has no available balance for mixed payment.'
                );
            }

            if ($walletAmount >= $orderTotal) {
                throw new DomainException(
                    'Wallet balance is sufficient for full order payment.'
                );
            }

            $gatewayAmount = $orderTotal - $walletAmount;

            $expiresAt = now()->addMinutes(
                (int)config(
                    'financial.wallet.top_up.expires_after_minutes',
                    30,
                ),
            );

            /*
             * Reserve the current wallet contribution.
             *
             * The hold references the Order directly because WalletTopUp
             * does not exist yet.
             */
            $walletHold = $this->createWalletHoldAction->execute(
                new CreateWalletHoldDTO(
                    wallet: $wallet,
                    type: WalletHoldTypeEnum::ORDER_PAYMENT,
                    amount: $walletAmount,
                    idempotencyKey: 'order-wallet-shortfall-hold:' . $requestKey,
                    actor: $user,
                    reference: $order,
                    description: 'رزرو موجودی برای پرداخت سفارش '
                    . $order->order_number,
                    metadata: [
                        ...$metadata,
                        'source' => 'order_wallet_shortfall',
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'order_total_amount' => $orderTotal,
                        'wallet_amount' => $walletAmount,
                        'gateway_amount' => $gatewayAmount,
                        'request_key' => $requestKey,
                    ],
                    expiresAt: $expiresAt,
                ),
            );

            /*
             * The shortage becomes a non-withdrawable top-up because
             * it will immediately be consumed by the related order.
             */
            $topUp = WalletTopUp::query()->create([
                'wallet_id' => $wallet->id,
                'order_id' => $order->id,
                'wallet_hold_id' => $walletHold->id,
                'request_key' => $requestKey,
                'purpose' => WalletTopUpPurposeEnum::ORDER_PAYMENT,
                'status' => WalletTopUpStatusEnum::PENDING_PAYMENT,
                'amount' => $gatewayAmount,
                'withdrawable_amount' => 0,

                'metadata' => [
                    ...$metadata,
                    'source' => 'order_wallet_shortfall',
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'order_total_amount' => $orderTotal,
                    'wallet_amount' => $walletAmount,
                    'gateway_amount' => $gatewayAmount,
                    'wallet_hold_id' => $walletHold->id,
                    'wallet_hold_token' => $walletHold->public_token,
                ],

                'expires_at' => $expiresAt,
            ]);

            /*
             * Payment amount comes from WalletTopUp::getPayableAmount(),
             * therefore only gatewayAmount is sent to the gateway.
             */
            $payment = $this->createPaymentForPayableAction->execute(
                payable: $topUp,
                gateway: $gateway,
                requestPayload: [
                    'callback_url' => $callbackUrl,

                    'metadata' => [
                        ...$metadata,
                        'source' => 'order_wallet_shortfall',
                        'user_id' => $user->id,
                        'order_id' => $order->id,
                        'order_number' => $order->order_number,
                        'wallet_top_up_id' => $topUp->id,
                        'wallet_top_up_token' => $topUp->public_token,
                        'wallet_hold_id' => $walletHold->id,
                        'wallet_amount' => $walletAmount,
                        'gateway_amount' => $gatewayAmount,
                        'description' => 'تکمیل موجودی برای پرداخت سفارش ' . $order->order_number,
                    ],
                ],
            );

            $topUp->forceFill([
                'payment_id' => $payment->id,
            ])->save();

            return $payment;
        }, 3);
    }

    /**
     * Start an already prepared external payment.
     *
     * This method must be called outside the database transaction
     * that created the order and wallet hold.
     *
     * @param array<string, mixed> $metadata
     */
    public function startPreparedPayment(
        Payment $payment,
        string  $callbackUrl,
        array   $metadata = [],
    ): Payment
    {
        $payment = $this->startCreatedPaymentAction->execute(
            payment: $payment,
            callbackUrl: $callbackUrl,
            metadata: $metadata,
        );

        if ($payment->status === PaymentStatusEnum::FAILED) {
            $this->failShortfallPaymentAction->execute(
                payment: $payment,
                reason: $payment->gateway_message ?: 'gateway_start_failed',
            );
        }

        return $payment->refresh()->loadMissing('payable');
    }

    private function findExistingPayment(
        string             $requestKey,
        Order              $order,
        User               $user,
        PaymentGatewayEnum $gateway,
    ): ?Payment
    {
        $topUp = WalletTopUp::query()
            ->with([
                'wallet',
                'payment',
                'walletHold',
                'order',
            ])
            ->where('request_key', $requestKey)
            ->first();

        if (!$topUp) {
            return null;
        }

        if (
            !$topUp->isOrderPayment()
            || (int)$topUp->order_id !== (int)$order->id
            || (int)$topUp->wallet?->user_id !== (int)$user->id
        ) {
            throw new WalletTopUpIdempotencyConflictException(
                'Wallet top-up request key belongs to another operation.'
            );
        }

        $payment = $topUp->payment;

        if (!$payment) {
            throw new WalletIntegrityException(
                'Order wallet shortfall top-up has no payment.'
            );
        }

        if ($payment->gateway !== $gateway) {
            throw new WalletTopUpIdempotencyConflictException(
                'Wallet top-up request key was created with another gateway.'
            );
        }

        if ((int)$payment->amount !== (int)$topUp->amount) {
            throw new WalletIntegrityException(
                'Wallet top-up amount does not match external payment amount.'
            );
        }

        if (
            $payment->payable_type !== $topUp->getMorphClass()
            || (int)$payment->payable_id !== (int)$topUp->id
        ) {
            throw new WalletIntegrityException(
                'External payment payable does not match wallet top-up.'
            );
        }

        return $payment;
    }

    private function validate(
        Order              $order,
        User               $user,
        PaymentGatewayEnum $gateway,
        string             $requestKey,
        string             $callbackUrl,
    ): void
    {
        if (!$order->exists) {
            throw new InvalidArgumentException(
                'Order must exist before starting wallet shortfall payment.'
            );
        }

        if (!$user->exists) {
            throw new InvalidArgumentException(
                'User must exist before starting wallet shortfall payment.'
            );
        }

        if (!$gateway->isExternal()) {
            throw new InvalidArgumentException(
                'Wallet shortfall payment requires an external gateway.'
            );
        }

        if (trim($requestKey) === '') {
            throw new InvalidArgumentException(
                'Wallet shortfall payment request key is required.'
            );
        }

        if (Str::length($requestKey) > 64) {
            throw new InvalidArgumentException(
                'Wallet shortfall payment request key cannot exceed 64 characters.'
            );
        }

        if (filter_var($callbackUrl, FILTER_VALIDATE_URL) === false) {
            throw new InvalidArgumentException(
                'Wallet shortfall payment callback URL is invalid.'
            );
        }
    }
}

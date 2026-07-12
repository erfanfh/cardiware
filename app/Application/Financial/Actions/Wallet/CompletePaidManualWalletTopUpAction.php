<?php

namespace App\Application\Financial\Actions\Wallet;

use App\Application\Financial\DTOs\CreditWalletData;
use App\Application\Financial\Exceptions\WalletIntegrityException;
use App\Application\Shared\Cache\Invalidators\UserCacheInvalidator;
use App\Domain\Financial\Enums\WalletTopUpStatusEnum;
use App\Domain\Financial\Enums\WalletTransactionTypeEnum;
use App\Domain\Financial\Models\Payment;
use App\Domain\Financial\Models\WalletTopUp;
use DomainException;
use Illuminate\Support\Facades\DB;
use Throwable;

class CompletePaidManualWalletTopUpAction
{
    public function __construct(
        private readonly CreditWalletAction $creditWalletAction,
    )
    {
    }

    /**
     * Complete a normal wallet top-up.
     *
     * @throws Throwable
     */
    public function execute(Payment $payment): WalletTopUp
    {
        $topUp = DB::transaction(function () use (
            $payment,
        ): WalletTopUp {
            $payment = Payment::query()
                ->whereKey($payment->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$payment->isPaid()) {
                throw new DomainException(
                    'Only paid payments can complete wallet top-up.'
                );
            }

            $payable = $payment->payable;

            if (!$payable instanceof WalletTopUp) {
                throw new DomainException(
                    'Payment payable is not a wallet top-up.'
                );
            }

            /** @var WalletTopUp $topUp */
            $topUp = WalletTopUp::query()
                ->with('wallet.user')
                ->whereKey($payable->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!$topUp->isManual()) {
                throw new DomainException(
                    'Wallet top-up is not a manual top-up.'
                );
            }

            if ($topUp->isCompleted()) {
                if (!$topUp->credited_transaction_id) {
                    throw new WalletIntegrityException(
                        'Completed wallet top-up has no credit transaction.'
                    );
                }

                return $topUp;
            }

            if ($topUp->status !== WalletTopUpStatusEnum::PENDING_PAYMENT) {
                throw new WalletIntegrityException(
                    'Paid wallet top-up is not in pending payment status.'
                );
            }

            if ((int)$topUp->payment_id !== (int)$payment->id) {
                throw new WalletIntegrityException(
                    'Wallet top-up payment does not match the paid payment.'
                );
            }

            if ((int)$payment->amount !== (int)$topUp->amount) {
                throw new WalletIntegrityException(
                    'Wallet top-up amount does not match payment amount.'
                );
            }

            $user = $topUp->wallet?->user;

            if (!$user) {
                throw new WalletIntegrityException(
                    'Wallet top-up owner could not be resolved.'
                );
            }

            $transaction = $this->creditWalletAction->execute(
                new CreditWalletData(
                    wallet: $topUp->wallet,
                    type: WalletTransactionTypeEnum::TOP_UP,
                    amount: (int)$topUp->amount,
                    idempotencyKey: 'wallet-top-up:' . $topUp->public_token,
                    withdrawableAmount: (int)$topUp->withdrawable_amount,
                    actor: $user,
                    reference: $topUp,
                    description: 'شارژ کیف پول از طریق درگاه پرداخت',
                    metadata: [
                        'purpose' => $topUp->purpose->value,
                        'payment_id' => $payment->id,
                        'payment_token' => $payment->public_token,
                        'gateway' => $payment->gateway->value,
                    ],
                ),
            );

            $topUp->forceFill([
                'status' => WalletTopUpStatusEnum::COMPLETED,
                'credited_transaction_id' => $transaction->id,
                'completed_at' => now(),
                'failed_at' => null,
                'failure_reason' => null,
            ])->save();

            return $topUp->refresh()->load([
                'wallet.user',
                'payment',
                'creditedTransaction',
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
}

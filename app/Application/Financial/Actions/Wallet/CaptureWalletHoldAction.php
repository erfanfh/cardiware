<?php

namespace App\Application\Financial\Actions\Wallet;

use App\Application\Financial\DTOs\DebitWalletDTO;
use App\Application\Financial\Exceptions\WalletHoldUnavailableException;
use App\Application\Financial\Exceptions\WalletIntegrityException;
use App\Application\Financial\Support\WalletBalanceCalculator;
use App\Application\Financial\Support\WalletTransactionFingerprint;
use App\Domain\Financial\Enums\WalletHoldStatusEnum;
use App\Domain\Financial\Enums\WalletTransactionDirectionEnum;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Financial\Models\WalletHold;
use App\Domain\Financial\Models\WalletTransaction;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class CaptureWalletHoldAction
{
    public function __construct(
        private readonly WalletBalanceCalculator      $walletBalanceCalculator,
        private readonly WalletTransactionFingerprint $walletTransactionFingerprint,
    )
    {
    }

    /**
     * Capture reserved balance and create a confirmed ledger entry.
     *
     * The resolvedBy argument represents the user or admin that
     * finalized the hold. The financial transaction actor remains
     * the original hold actor.
     *
     * @throws Throwable
     */
    public function execute(
        WalletHold $hold,
        ?User      $resolvedBy = null,
    ): WalletTransaction
    {
        if (!$hold->exists) {
            throw new InvalidArgumentException(
                'Wallet hold must exist before being captured.'
            );
        }

        if ($resolvedBy && !$resolvedBy->exists) {
            throw new InvalidArgumentException(
                'Wallet hold resolver must exist.'
            );
        }

        $walletId = (int)$hold->wallet_id;
        $holdId = (int)$hold->id;

        return DB::transaction(function () use (
            $walletId,
            $holdId,
            $resolvedBy,
        ): WalletTransaction {
            /*
             * Keep lock order consistent with ReleaseWalletHoldAction.
             */
            /** @var Wallet $wallet */
            $wallet = Wallet::query()
                ->whereKey($walletId)
                ->lockForUpdate()
                ->firstOrFail();

            /** @var WalletHold $hold */
            $hold = WalletHold::query()
                ->whereKey($holdId)
                ->lockForUpdate()
                ->firstOrFail();

            /*
             * Idempotent duplicate capture.
             */
            if ($hold->isCaptured()) {
                $transaction = $hold->capturedTransaction()->first();

                if (!$transaction) {
                    throw new WalletIntegrityException(
                        'Captured hold has no wallet transaction.'
                    );
                }

                return $transaction->loadMissing([
                    'wallet',
                    'actor',
                    'reference',
                ]);
            }

            if (!$hold->isActive()) {
                throw new WalletHoldUnavailableException(
                    'Only active wallet holds can be captured.'
                );
            }

            $this->walletBalanceCalculator->assertIntegrity($wallet);

            if ((int)$wallet->held_balance < (int)$hold->amount) {
                throw new WalletIntegrityException(
                    'Wallet held balance is lower than captured hold amount.'
                );
            }

            if ((int)$wallet->held_withdrawable_balance < (int)$hold->withdrawable_amount) {
                throw new WalletIntegrityException(
                    'Wallet held withdrawable balance is lower than captured hold amount.'
                );
            }

            if ((int)$wallet->balance < (int)$hold->amount) {
                throw new WalletIntegrityException(
                    'Wallet balance is lower than captured hold amount.'
                );
            }

            if ((int)$wallet->withdrawable_balance < (int)$hold->withdrawable_amount) {
                throw new WalletIntegrityException(
                    'Wallet withdrawable balance is lower than captured hold amount.'
                );
            }

            $balanceBefore = (int)$wallet->balance;
            $withdrawableBalanceBefore = (int)$wallet->withdrawable_balance;
            $balanceAfter = $balanceBefore - (int)$hold->amount;
            $withdrawableBalanceAfter = $withdrawableBalanceBefore - (int)$hold->withdrawable_amount;

            $wallet->forceFill([
                'balance' => $balanceAfter,
                'withdrawable_balance' => $withdrawableBalanceAfter,
                'held_balance' => (int)$wallet->held_balance - (int)$hold->amount,
                'held_withdrawable_balance' => (int)$wallet->held_withdrawable_balance - (int)$hold->withdrawable_amount,
            ])->save();

            $this->walletBalanceCalculator->assertIntegrity($wallet);

            $idempotencyKey = 'wallet-hold-capture:' . $hold->public_token;

            /*
             * Reuse the existing debit fingerprint structure so direct
             * debits and hold captures share the same financial intent rules.
             */
            $debitData = new DebitWalletDTO(
                wallet: $wallet,
                type: $hold->type->transactionType(),
                amount: (int)$hold->amount,
                idempotencyKey: $idempotencyKey,
                policy: $hold->debit_policy,
                actor: $hold->actor,
                reference: $hold->reference,
                description: $hold->description,
                metadata: $hold->metadata ?? [],
            );

            $fingerprint = $this->walletTransactionFingerprint->forDebit($debitData);

            $transaction = WalletTransaction::query()->create([
                'wallet_id' => $wallet->id,
                'actor_user_id' => $hold->actor_user_id,
                'type' => $hold->type->transactionType(),
                'direction' => WalletTransactionDirectionEnum::DEBIT,
                'amount' => $hold->amount,
                'withdrawable_amount' => $hold->withdrawable_amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'withdrawable_balance_before' => $withdrawableBalanceBefore,
                'withdrawable_balance_after' => $withdrawableBalanceAfter,
                'reference_type' => $hold->reference_type,
                'reference_id' => $hold->reference_id,
                'idempotency_key' => $idempotencyKey,
                'idempotency_fingerprint' => $fingerprint,
                'description' => $hold->description,
                'metadata' => array_merge(
                    $hold->metadata ?? [],
                    [
                        'wallet_hold_id' => $hold->id,
                        'wallet_hold_token' => $hold->public_token,
                        'wallet_hold_type' => $hold->type->value,
                        'debit_policy' => $hold->debit_policy->value,
                    ],
                ),
            ]);

            $hold->forceFill([
                'status' => WalletHoldStatusEnum::CAPTURED,
                'captured_transaction_id' => $transaction->id,
                'captured_by_user_id' => $resolvedBy?->getKey(),
                'captured_at' => now(),
            ])->save();

            return $transaction->load([
                'wallet',
                'actor',
                'reference',
            ]);
        }, 3);
    }
}

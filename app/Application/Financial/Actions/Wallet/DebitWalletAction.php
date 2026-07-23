<?php

namespace App\Application\Financial\Actions\Wallet;

use App\Application\Financial\DTOs\DebitWalletDTO;
use App\Application\Financial\Exceptions\WalletUnavailableException;
use App\Application\Financial\Support\WalletBalanceCalculator;
use App\Application\Financial\Support\WalletTransactionFingerprint;
use App\Application\Financial\Support\WalletTransactionIdempotencyGuard;
use App\Domain\Financial\Enums\WalletTransactionDirectionEnum;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Financial\Models\WalletTransaction;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class DebitWalletAction
{
    public function __construct(
        private readonly WalletBalanceCalculator           $walletBalanceCalculator,
        private readonly WalletTransactionFingerprint      $walletTransactionFingerprint,
        private readonly WalletTransactionIdempotencyGuard $idempotencyGuard,
    )
    {
    }

    /**
     * Atomically debit a wallet and create its ledger transaction.
     *
     * @throws Throwable
     */
    public function execute(
        DebitWalletDTO $data,
    ): WalletTransaction
    {
        $this->validate($data);

        $fingerprint = $this->walletTransactionFingerprint->forDebit($data);

        /*
         * A completed duplicate must be returned even if the wallet
         * was blocked after the original operation.
         */
        $existingTransaction = $this->idempotencyGuard->find(
            idempotencyKey: $data->idempotencyKey,
            fingerprint: $fingerprint,
        );

        if ($existingTransaction) {
            return $existingTransaction->loadMissing([
                'wallet',
                'actor',
                'reference',
            ]);
        }

        try {
            return DB::transaction(function () use (
                $data,
                $fingerprint,
            ): WalletTransaction {
                /** @var Wallet $wallet */
                $wallet = Wallet::query()
                    ->whereKey($data->wallet->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $existingTransaction = $this->idempotencyGuard->find(
                    idempotencyKey: $data->idempotencyKey,
                    fingerprint: $fingerprint,
                );

                if ($existingTransaction) {
                    return $existingTransaction->loadMissing([
                        'wallet',
                        'actor',
                        'reference',
                    ]);
                }

                if (!$wallet->allowsUserTransactions()) {
                    throw new WalletUnavailableException(
                        'Wallet is not available for debit operations.'
                    );
                }

                $this->walletBalanceCalculator->assertIntegrity($wallet);

                /*
                 * Determine how much of this debit is taken from
                 * the withdrawable balance bucket.
                 */
                $withdrawableDebitAmount = $this->walletBalanceCalculator
                    ->calculateWithdrawableDebit(
                        wallet: $wallet,
                        amount: $data->amount,
                        policy: $data->policy,
                    );

                $balanceBefore = (int)$wallet->balance;

                $withdrawableBalanceBefore = (int)$wallet->withdrawable_balance;

                $balanceAfter = $balanceBefore - $data->amount;

                $withdrawableBalanceAfter = $withdrawableBalanceBefore - $withdrawableDebitAmount;

                $wallet->forceFill([
                    'balance' => $balanceAfter,
                    'withdrawable_balance' => $withdrawableBalanceAfter,
                ])->save();

                $this->walletBalanceCalculator->assertIntegrity($wallet);

                $transaction = WalletTransaction::query()->create([
                    'wallet_id' => $wallet->id,

                    'actor_user_id' => $data->actor?->getKey(),

                    'type' => $data->type,

                    'direction' => WalletTransactionDirectionEnum::DEBIT,

                    'amount' => $data->amount,

                    /*
                     * For debit transactions, this represents
                     * the amount removed from withdrawable balance.
                     */
                    'withdrawable_amount' => $withdrawableDebitAmount,

                    'balance_before' => $balanceBefore,

                    'balance_after' => $balanceAfter,

                    'withdrawable_balance_before' => $withdrawableBalanceBefore,

                    'withdrawable_balance_after' => $withdrawableBalanceAfter,

                    'reference_type' => $data->reference?->getMorphClass(),

                    'reference_id' => $data->reference?->getKey(),

                    'idempotency_key' => $data->idempotencyKey,

                    'idempotency_fingerprint' => $fingerprint,

                    'description' => $data->description,

                    'metadata' => array_merge(
                        $data->metadata,
                        [
                            'debit_policy' =>
                                $data->policy->value,
                        ],
                    ),
                ]);

                return $transaction->load([
                    'wallet',
                    'actor',
                    'reference',
                ]);
            }, 3);
        } catch (QueryException $exception) {
            $existingTransaction = $this->idempotencyGuard->find(
                idempotencyKey: $data->idempotencyKey,
                fingerprint: $fingerprint,
            );

            if ($existingTransaction) {
                return $existingTransaction->loadMissing([
                    'wallet',
                    'actor',
                    'reference',
                ]);
            }

            throw $exception;
        }
    }

    private function validate(DebitWalletDTO $data): void
    {
        if (!$data->wallet->exists) {
            throw new InvalidArgumentException(
                'Wallet must exist before being debited.'
            );
        }

        if (!$data->type->direction()->isDebit()) {
            throw new InvalidArgumentException(
                'Wallet transaction type is not a debit type.'
            );
        }

        if ($data->amount <= 0) {
            throw new InvalidArgumentException(
                'Wallet debit amount must be greater than zero.'
            );
        }

        $idempotencyKey = trim($data->idempotencyKey);

        if ($idempotencyKey === '') {
            throw new InvalidArgumentException(
                'Wallet idempotency key is required.'
            );
        }

        if (Str::length($idempotencyKey) > 160) {
            throw new InvalidArgumentException(
                'Wallet idempotency key cannot exceed 160 characters.'
            );
        }

        if ($data->actor && !$data->actor->exists) {
            throw new InvalidArgumentException(
                'Wallet transaction actor must exist.'
            );
        }

        if ($data->reference && !$data->reference->exists) {
            throw new InvalidArgumentException(
                'Wallet transaction reference must exist.'
            );
        }
    }
}

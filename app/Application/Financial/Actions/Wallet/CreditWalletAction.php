<?php

namespace App\Application\Financial\Actions\Wallet;

use App\Application\Financial\DTOs\CreditWalletDTO;
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

class CreditWalletAction
{
    public function __construct(
        private readonly WalletBalanceCalculator           $walletBalanceCalculator,
        private readonly WalletTransactionFingerprint      $walletTransactionFingerprint,
        private readonly WalletTransactionIdempotencyGuard $idempotencyGuard,
    )
    {
    }

    /**
     * Atomically credit a wallet and create its ledger transaction.
     *
     * @throws Throwable
     */
    public function execute(
        CreditWalletDTO $data,
    ): WalletTransaction
    {
        $this->validate($data);

        $fingerprint = $this->walletTransactionFingerprint->forCredit($data);

        /*
         * Fast idempotency check before acquiring a wallet lock.
         */
        $existingTransaction =
            $this->idempotencyGuard->find(
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

                /*
                 * Check again after obtaining the wallet lock.
                 */
                $existingTransaction =
                    $this->idempotencyGuard->find(
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

                $this->walletBalanceCalculator
                    ->assertIntegrity($wallet);

                $balanceBefore = (int)$wallet->balance;

                $withdrawableBalanceBefore = (int)$wallet->withdrawable_balance;

                $balanceAfter = $balanceBefore + $data->amount;

                $withdrawableBalanceAfter = $withdrawableBalanceBefore + $data->withdrawableAmount;

                $wallet->forceFill([
                    'balance' => $balanceAfter,

                    'withdrawable_balance' => $withdrawableBalanceAfter,
                ])->save();

                $this->walletBalanceCalculator
                    ->assertIntegrity($wallet);

                $transaction = WalletTransaction::query()->create([
                    'wallet_id' => $wallet->id,

                    'actor_user_id' => $data->actor?->getKey(),

                    'type' => $data->type,

                    'direction' => WalletTransactionDirectionEnum::CREDIT,

                    'amount' => $data->amount,

                    'withdrawable_amount' => $data->withdrawableAmount,

                    'balance_before' => $balanceBefore,

                    'balance_after' => $balanceAfter,

                    'withdrawable_balance_before' => $withdrawableBalanceBefore,

                    'withdrawable_balance_after' => $withdrawableBalanceAfter,

                    'reference_type' => $data->reference?->getMorphClass(),

                    'reference_id' => $data->reference?->getKey(),

                    'idempotency_key' => $data->idempotencyKey,

                    'idempotency_fingerprint' => $fingerprint,

                    'description' => $data->description,

                    'metadata' => $data->metadata,
                ]);

                return $transaction->load([
                    'wallet',
                    'actor',
                    'reference',
                ]);
            }, 3);
        } catch (QueryException $exception) {
            /*
             * Handles a rare race where two processes attempted
             * the same global idempotency key concurrently.
             */
            $existingTransaction =
                $this->idempotencyGuard->find(
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

    private function validate(CreditWalletDTO $data): void
    {
        if (!$data->wallet->exists) {
            throw new InvalidArgumentException(
                'Wallet must exist before being credited.'
            );
        }

        if (!$data->type->direction()->isCredit()) {
            throw new InvalidArgumentException(
                'Wallet transaction type is not a credit type.'
            );
        }

        if ($data->amount <= 0) {
            throw new InvalidArgumentException(
                'Wallet credit amount must be greater than zero.'
            );
        }

        if ($data->withdrawableAmount < 0) {
            throw new InvalidArgumentException(
                'Withdrawable credit amount cannot be negative.'
            );
        }

        if ($data->withdrawableAmount > $data->amount) {
            throw new InvalidArgumentException(
                'Withdrawable credit amount cannot exceed total amount.'
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

<?php

namespace App\Application\Financial\Actions\Wallet;

use App\Application\Financial\DTOs\CreateWalletHoldData;
use App\Application\Financial\Exceptions\WalletUnavailableException;
use App\Application\Financial\Support\WalletBalanceCalculator;
use App\Application\Financial\Support\WalletHoldFingerprint;
use App\Application\Financial\Support\WalletHoldIdempotencyGuard;
use App\Domain\Financial\Enums\WalletHoldStatusEnum;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Financial\Models\WalletHold;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class CreateWalletHoldAction
{
    public function __construct(
        private readonly WalletBalanceCalculator    $walletBalanceCalculator,
        private readonly WalletHoldFingerprint      $walletHoldFingerprint,
        private readonly WalletHoldIdempotencyGuard $idempotencyGuard,
    )
    {
    }

    /**
     * Atomically reserve wallet balance.
     *
     * @throws Throwable
     */
    public function execute(
        CreateWalletHoldData $data,
    ): WalletHold
    {
        $this->validate($data);

        $fingerprint = $this->walletHoldFingerprint->forCreate($data);

        /*
         * Return an existing operation before checking wallet status.
         *
         * This preserves idempotency even if the wallet was blocked
         * after the original hold was created.
         */
        $existingHold = $this->idempotencyGuard->find(
            idempotencyKey: $data->idempotencyKey,
            fingerprint: $fingerprint,
        );

        if ($existingHold) {
            return $existingHold->loadMissing([
                'wallet',
                'actor',
                'reference',
                'capturedTransaction',
            ]);
        }

        try {
            return DB::transaction(function () use (
                $data,
                $fingerprint,
            ): WalletHold {
                /** @var Wallet $wallet */
                $wallet = Wallet::query()
                    ->whereKey($data->wallet->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                /*
                 * Check again after obtaining wallet lock.
                 */
                $existingHold = $this->idempotencyGuard->find(
                    idempotencyKey: $data->idempotencyKey,
                    fingerprint: $fingerprint,
                );

                if ($existingHold) {
                    return $existingHold->loadMissing([
                        'wallet',
                        'actor',
                        'reference',
                        'capturedTransaction',
                    ]);
                }

                if (!$wallet->allowsUserTransactions()) {
                    throw new WalletUnavailableException(
                        'Wallet is not available for hold operations.'
                    );
                }

                $this->walletBalanceCalculator->assertIntegrity($wallet);

                $debitPolicy = $data->type->debitPolicy();

                /*
                 * Determine how much of this hold must be reserved
                 * from the withdrawable bucket.
                 */
                $withdrawableHoldAmount = $this->walletBalanceCalculator
                    ->calculateWithdrawableDebit(
                        wallet: $wallet,
                        amount: $data->amount,
                        policy: $debitPolicy,
                    );

                $wallet->forceFill([
                    'held_balance' => (int)$wallet->held_balance + $data->amount,
                    'held_withdrawable_balance' => (int)$wallet->held_withdrawable_balance + $withdrawableHoldAmount,
                ])->save();

                $this->walletBalanceCalculator->assertIntegrity($wallet);

                $hold = WalletHold::query()->create([
                    'wallet_id' => $wallet->id,
                    'actor_user_id' => $data->actor?->getKey(),
                    'type' => $data->type,
                    'status' => WalletHoldStatusEnum::ACTIVE,
                    'debit_policy' => $debitPolicy,
                    'amount' => $data->amount,
                    'withdrawable_amount' => $withdrawableHoldAmount,
                    'reference_type' => $data->reference?->getMorphClass(),
                    'reference_id' => $data->reference?->getKey(),
                    'idempotency_key' => $data->idempotencyKey,
                    'idempotency_fingerprint' => $fingerprint,
                    'description' => $data->description,
                    'metadata' => $data->metadata,
                    'expires_at' => $data->expiresAt,
                ]);

                return $hold->load([
                    'wallet',
                    'actor',
                    'reference',
                ]);
            }, 3);
        } catch (QueryException $exception) {
            /*
             * Handles concurrent attempts with the same global key.
             * The failed database transaction has already rolled back
             * wallet held balances.
             */
            $existingHold = $this->idempotencyGuard->find(
                idempotencyKey: $data->idempotencyKey,
                fingerprint: $fingerprint,
            );

            if ($existingHold) {
                return $existingHold->loadMissing([
                    'wallet',
                    'actor',
                    'reference',
                    'capturedTransaction',
                ]);
            }

            throw $exception;
        }
    }

    private function validate(
        CreateWalletHoldData $data,
    ): void
    {
        if (!$data->wallet->exists) {
            throw new InvalidArgumentException(
                'Wallet must exist before creating a hold.'
            );
        }

        if ($data->amount <= 0) {
            throw new InvalidArgumentException(
                'Wallet hold amount must be greater than zero.'
            );
        }

        $idempotencyKey = trim($data->idempotencyKey);

        if ($idempotencyKey === '') {
            throw new InvalidArgumentException(
                'Wallet hold idempotency key is required.'
            );
        }

        if (Str::length($idempotencyKey) > 160) {
            throw new InvalidArgumentException(
                'Wallet hold idempotency key cannot exceed 160 characters.'
            );
        }

        if ($data->actor && !$data->actor->exists) {
            throw new InvalidArgumentException(
                'Wallet hold actor must exist.'
            );
        }

        if ($data->reference && !$data->reference->exists) {
            throw new InvalidArgumentException(
                'Wallet hold reference must exist.'
            );
        }

        if ($data->expiresAt && $data->expiresAt->lessThanOrEqualTo(now())) {
            throw new InvalidArgumentException(
                'Wallet hold expiry time must be in the future.'
            );
        }
    }
}

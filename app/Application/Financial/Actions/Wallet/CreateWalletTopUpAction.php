<?php

namespace App\Application\Financial\Actions\Wallet;

use App\Application\Financial\Exceptions\WalletTopUpIdempotencyConflictException;
use App\Application\Financial\Exceptions\WalletUnavailableException;
use App\Domain\Financial\Enums\WalletTopUpStatusEnum;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Financial\Models\WalletTopUp;
use App\Domain\Identity\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Throwable;

class CreateWalletTopUpAction
{
    public function __construct(
        private readonly GetOrCreateWalletAction $getOrCreateWalletAction,
    )
    {
    }

    /**
     * @param array<string, mixed> $metadata
     * @throws Throwable
     */
    public function execute(
        User   $user,
        int    $amount,
        string $requestKey,
        array  $metadata = [],
    ): WalletTopUp
    {
        $this->validate(
            user: $user,
            amount: $amount,
            requestKey: $requestKey,
        );

        $existing = $this->findExisting(
            requestKey: $requestKey,
            user: $user,
            amount: $amount,
        );

        if ($existing) {
            return $existing;
        }

        $wallet = $this->getOrCreateWalletAction->execute($user);

        try {
            return DB::transaction(function () use (
                $wallet,
                $user,
                $amount,
                $requestKey,
                $metadata,
            ): WalletTopUp {
                /** @var Wallet $wallet */
                $wallet = Wallet::query()
                    ->whereKey($wallet->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $existing = $this->findExisting(
                    requestKey: $requestKey,
                    user: $user,
                    amount: $amount,
                );

                if ($existing) {
                    return $existing;
                }

                if (!$wallet->allowsUserTransactions()) {
                    throw new WalletUnavailableException(
                        'Wallet is not available for top-up operations.'
                    );
                }

                $withdrawableAmount = config(
                    'financial.wallet.top_up.is_withdrawable',
                    false,
                )
                    ? $amount
                    : 0;

                return WalletTopUp::query()->create([
                    'wallet_id' => $wallet->id,
                    'request_key' => $requestKey,
                    'status' => WalletTopUpStatusEnum::PENDING_PAYMENT,
                    'amount' => $amount,
                    'withdrawable_amount' => $withdrawableAmount,
                    'metadata' => $metadata,
                    'expires_at' => now()->addMinutes(
                        (int)config(
                            'financial.wallet.top_up.expires_after_minutes',
                            30,
                        ),
                    ),
                ]);
            }, 3);
        } catch (QueryException $exception) {
            $existing = $this->findExisting(
                requestKey: $requestKey,
                user: $user,
                amount: $amount,
            );

            if ($existing) {
                return $existing;
            }

            throw $exception;
        }
    }

    private function validate(
        User   $user,
        int    $amount,
        string $requestKey,
    ): void
    {
        if (!$user->exists) {
            throw new InvalidArgumentException(
                'Wallet top-up user must exist.'
            );
        }

        $minimumAmount = (int)config(
            'financial.wallet.top_up.minimum_amount',
            50_000,
        );

        $maximumAmount = (int)config(
            'financial.wallet.top_up.maximum_amount',
            20_000_000,
        );

        if ($amount < $minimumAmount) {
            throw new InvalidArgumentException(
                'Wallet top-up amount is below the allowed minimum.'
            );
        }

        if ($amount > $maximumAmount) {
            throw new InvalidArgumentException(
                'Wallet top-up amount exceeds the allowed maximum.'
            );
        }

        if (trim($requestKey) === '') {
            throw new InvalidArgumentException(
                'Wallet top-up request key is required.'
            );
        }

        if (Str::length($requestKey) > 64) {
            throw new InvalidArgumentException(
                'Wallet top-up request key cannot exceed 64 characters.'
            );
        }
    }

    private function findExisting(
        string $requestKey,
        User   $user,
        int    $amount,
    ): ?WalletTopUp
    {
        $topUp = WalletTopUp::query()
            ->with('wallet')
            ->where('request_key', $requestKey)
            ->first();

        if (!$topUp) {
            return null;
        }

        if (
            (int)$topUp->wallet?->user_id !== (int)$user->id
            || (int)$topUp->amount !== $amount
        ) {
            throw new WalletTopUpIdempotencyConflictException(
                'The wallet top-up request key has already been used for another request.'
            );
        }

        return $topUp;
    }
}

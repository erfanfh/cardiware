<?php

namespace App\Application\Financial\Actions\Wallet;

use App\Application\Financial\Exceptions\WalletIntegrityException;
use App\Application\Financial\Support\WalletBalanceCalculator;
use App\Domain\Financial\Enums\WalletHoldStatusEnum;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Financial\Models\WalletHold;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

class ExpireOverdueWalletHoldsAction
{
    public function __construct(
        private readonly WalletBalanceCalculator $walletBalanceCalculator,
    )
    {
    }

    /**
     * Expire active wallet holds whose expiration time has passed.
     *
     * @throws Throwable
     */
    public function execute(?CarbonInterface $now = null, int $chunkSize = 200): int
    {
        $now ??= now();

        $expiredCount = 0;

        WalletHold::query()
            ->select([
                'id',
                'wallet_id',
            ])
            ->where('status', WalletHoldStatusEnum::ACTIVE->value)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', $now)
            ->orderBy('id')
            ->chunkById(
                $chunkSize,
                function ($walletHolds) use ($now, &$expiredCount): void {
                    foreach ($walletHolds as $walletHold) {
                        if ($this->expireOne(
                            walletHoldId: (int)$walletHold->id,
                            walletId: (int)$walletHold->wallet_id,
                            now: $now,
                        )) {
                            $expiredCount++;
                        }
                    }
                }
            );

        return $expiredCount;
    }

    /**
     * @throws Throwable
     */
    private function expireOne(int $walletHoldId, int $walletId, CarbonInterface $now): bool
    {
        return DB::transaction(function () use (
            $walletHoldId,
            $walletId,
            $now,
        ): bool {
            /*
             * Lock order:
             *
             * 1. Wallet
             * 2. WalletHold
             *
             * This matches the existing checkout failure action.
             */

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

            /*
             * The hold may have been captured, released or expired
             * after the initial candidate query.
             */
            if ($walletHold->status !== WalletHoldStatusEnum::ACTIVE) {
                return false;
            }

            if ($walletHold->expires_at === null || $walletHold->expires_at->isAfter($now)) {
                return false;
            }

            if ((int)$walletHold->wallet_id !== (int)$wallet->id) {
                throw new WalletIntegrityException(
                    'Wallet hold does not belong to wallet.'
                );
            }

            $this->walletBalanceCalculator->assertIntegrity($wallet);

            $holdAmount = (int)$walletHold->amount;

            $holdWithdrawableAmount = (int)$walletHold->withdrawable_amount;

            if ((int)$wallet->held_balance < $holdAmount) {
                throw new WalletIntegrityException(
                    'Wallet held balance is lower than wallet hold amount.'
                );
            }

            if ((int)$wallet->held_withdrawable_balance < $holdWithdrawableAmount) {
                throw new WalletIntegrityException(
                    'Wallet held withdrawable balance is lower than wallet hold withdrawable amount.'
                );
            }

            $wallet->forceFill([
                'held_balance' => (int)$wallet->held_balance - $holdAmount,
                'held_withdrawable_balance' => (int)$wallet->held_withdrawable_balance - $holdWithdrawableAmount,
            ])->save();

            $walletHold->forceFill([
                'status' => WalletHoldStatusEnum::EXPIRED,
            ])->save();

            $this->walletBalanceCalculator->assertIntegrity(
                $wallet->refresh()
            );

            return true;
        }, 3);
    }
}

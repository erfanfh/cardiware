<?php

namespace App\Application\Financial\Actions\Wallet;

use App\Application\Financial\Exceptions\WalletHoldUnavailableException;
use App\Application\Financial\Exceptions\WalletIntegrityException;
use App\Application\Financial\Support\WalletBalanceCalculator;
use App\Domain\Financial\Enums\WalletHoldStatusEnum;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Financial\Models\WalletHold;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class ReleaseWalletHoldAction
{
    public function __construct(
        private readonly WalletBalanceCalculator $walletBalanceCalculator,
    )
    {
    }

    /**
     * Release reserved balance without changing confirmed balance.
     *
     * @throws Throwable
     */
    public function execute(
        WalletHold $hold,
        ?User      $releasedBy = null,
        ?string    $reason = null,
    ): WalletHold
    {
        if (!$hold->exists) {
            throw new InvalidArgumentException(
                'Wallet hold must exist before being released.'
            );
        }

        if ($releasedBy && !$releasedBy->exists) {
            throw new InvalidArgumentException(
                'Wallet hold resolver must exist.'
            );
        }

        $walletId = (int)$hold->wallet_id;
        $holdId = (int)$hold->id;

        return DB::transaction(function () use (
            $walletId,
            $holdId,
            $releasedBy,
            $reason,
        ): WalletHold {
            /*
             * Lock order is always:
             * 1. Wallet
             * 2. Hold
             *
             * Capture action uses the same order to reduce deadlock risk.
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

            if ($hold->isReleased() || $hold->isExpired()) {
                return $hold->loadMissing([
                    'wallet',
                    'actor',
                    'reference',
                    'releasedBy',
                ]);
            }

            if ($hold->isCaptured()) {
                throw new WalletHoldUnavailableException(
                    'Captured wallet hold cannot be released.'
                );
            }

            if (!$hold->isActive()) {
                throw new WalletHoldUnavailableException(
                    'Wallet hold is not active.'
                );
            }

            $this->walletBalanceCalculator->assertIntegrity($wallet);

            if ((int)$wallet->held_balance < (int)$hold->amount) {
                throw new WalletIntegrityException(
                    'Wallet held balance is lower than hold amount.'
                );
            }

            if ((int)$wallet->held_withdrawable_balance < (int)$hold->withdrawable_amount) {
                throw new WalletIntegrityException(
                    'Wallet held withdrawable balance is lower than hold amount.'
                );
            }

            $wallet->forceFill([
                'held_balance' => (int)$wallet->held_balance - (int)$hold->amount,
                'held_withdrawable_balance' => (int)$wallet->held_withdrawable_balance - (int)$hold->withdrawable_amount,
            ])->save();

            $this->walletBalanceCalculator->assertIntegrity($wallet);

            $hold->forceFill([
                'status' => WalletHoldStatusEnum::RELEASED,
                'released_by_user_id' => $releasedBy?->getKey(),
                'release_reason' => $reason,
                'released_at' => now(),
            ])->save();

            return $hold->refresh()->load([
                'wallet',
                'actor',
                'reference',
                'releasedBy',
            ]);
        }, 3);
    }
}

<?php

namespace App\Application\Financial\Actions\Wallet;

use App\Domain\Financial\Enums\WalletStatusEnum;
use App\Domain\Financial\Models\Wallet;
use App\Domain\Identity\Models\User;

class GetOrCreateWalletAction
{
    /**
     * Get the user's wallet or create it safely.
     */
    public function execute(User $user): Wallet
    {
        $now = now();

        /*
         * insertOrIgnore makes concurrent wallet creation safe.
         * The unique user_id index guarantees one wallet per user.
         */
        Wallet::query()->insertOrIgnore([
            'user_id' => $user->id,
            'status' => WalletStatusEnum::ACTIVE,

            'balance' => 0,
            'withdrawable_balance' => 0,

            'held_balance' => 0,
            'held_withdrawable_balance' => 0,

            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return Wallet::query()
            ->where('user_id', $user->id)
            ->firstOrFail();
    }
}

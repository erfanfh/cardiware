<?php

namespace App\Application\Loyalty\Actions;

use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\PointAccount;

class GetOrCreatePointAccountAction
{
    public function execute(User $user): PointAccount
    {
        $now = now();

        PointAccount::query()->insertOrIgnore([
            'user_id' => $user->id,
            'balance' => 0,
            'lifetime_earned' => 0,
            'lifetime_spent' => 0,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return PointAccount::query()
            ->where('user_id', $user->id)
            ->firstOrFail();
    }
}

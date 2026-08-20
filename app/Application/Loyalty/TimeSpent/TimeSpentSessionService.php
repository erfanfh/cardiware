<?php

namespace App\Application\Loyalty\TimeSpent;

use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\LoyaltyTimeSpentStatusEnum;
use App\Domain\Loyalty\Models\EarningRule;
use App\Domain\Loyalty\Models\LoyaltyTimeSpentSession;
use Illuminate\Support\Str;

class TimeSpentSessionService
{
    public function start(
        User $user,
        string $targetType,
        int $targetId,
        EarningRule $rule,
    ): LoyaltyTimeSpentSession {
        return LoyaltyTimeSpentSession::firstOrCreate(
            [
                'user_id' => $user->id,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'status' => LoyaltyTimeSpentStatusEnum::PENDING,
            ],
            [
                'earning_rule_id' => $rule->id,
                'token' => Str::uuid()->toString(),
                'started_at' => now(),
            ]
        );
    }
}

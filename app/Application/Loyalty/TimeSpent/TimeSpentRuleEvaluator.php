<?php

namespace App\Application\Loyalty\TimeSpent;

use App\Application\Loyalty\Earning\Support\EarningRuleUsageCounter;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Models\EarningRule;
use App\Domain\Loyalty\Models\LoyaltyTimeSpentSession;

class TimeSpentRuleEvaluator
{
    public function __construct(
        private readonly EarningRuleUsageCounter $usageCounter,
    ) {
    }

    public function evaluate(
        User $user,
        EarningRule $rule,
        LoyaltyTimeSpentSession $session,
    ): ?string {
        $config = $rule->config ?? [];

        $requiredSeconds = (int) ($config['required_seconds'] ?? 0);

        if ($requiredSeconds > 0 && $session->duration_seconds < $requiredSeconds) {
            return 'required_seconds_not_reached';
        }

        $dailyLimit = (int) ($config['daily_limit'] ?? 0);

        if ($this->usageCounter->hasReachedDailyLimit($user, $rule, $dailyLimit, now())) {
            return 'daily_limit_reached';
        }

        return null;
    }
}

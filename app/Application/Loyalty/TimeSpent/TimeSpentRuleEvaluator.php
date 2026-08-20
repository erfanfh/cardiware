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
        LoyaltyTimeSpentSession $session,
        EarningRule $rule,
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

        if (($config['unique_target_daily'] ?? false) === true) {
            $exists = LoyaltyTimeSpentSession::query()
                ->where('user_id', $user->id)
                ->where('earning_rule_id', $rule->id)
                ->where('target_type', $session->target_type)
                ->where('target_id', $session->target_id)
                ->whereDate('created_at', today())
                ->whereKeyNot($session->id)
                ->whereNotNull('completed_at')
                ->exists();

            if ($exists) {
                return 'duplicate_target_daily';
            }
        }

        return null;
    }
}

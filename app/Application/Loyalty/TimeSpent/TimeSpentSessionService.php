<?php

namespace App\Application\Loyalty\TimeSpent;

use App\Application\Loyalty\Earning\Actions\ProcessEarningRuleAction;
use App\Application\Loyalty\Earning\Contexts\TimeSpentEarningContext;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Enums\LoyaltyTimeSpentStatusEnum;
use App\Domain\Loyalty\Models\LoyaltyTimeSpentSession;
use Illuminate\Support\Facades\DB;

class TimeSpentSessionService
{
    public function __construct(
        private readonly ProcessEarningRuleAction $processEarningRuleAction,
        private readonly TimeSpentRuleEvaluator $evaluator,
    ) {
    }

    public function complete(User $user, string $token): LoyaltyTimeSpentSession
    {
        return DB::transaction(function () use ($user, $token) {
            $session = LoyaltyTimeSpentSession::query()
                ->where('user_id', $user->id)
                ->where('token', $token)
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->status !== LoyaltyTimeSpentStatusEnum::PENDING) {
                return $session;
            }

            $duration = now()->diffInSeconds($session->started_at);
            $rule = $session->earningRule;

            $reason = $this->evaluator->evaluate($user, $rule, $session->setAttribute('duration_seconds', $duration));

            if ($reason) {
                $session->update([
                    'duration_seconds' => $duration,
                    'completed_at' => now(),
                    'status' => LoyaltyTimeSpentStatusEnum::REJECTED,
                    'rejection_reason' => $reason,
                ]);

                return $session->refresh();
            }

            $session->update([
                'duration_seconds' => $duration,
                'completed_at' => now(),
                'status' => LoyaltyTimeSpentStatusEnum::COMPLETED,
            ]);

            $this->processEarningRuleAction->execute(
                EarningRuleTypeEnum::TIME_SPENT,
                new TimeSpentEarningContext(
                    user: $user,
                    targetType: $session->target_type,
                    targetId: $session->target_id,
                    durationSeconds: $duration,
                    occurredAt: now(),
                ),
            );

            return $session->refresh();
        });
    }
}

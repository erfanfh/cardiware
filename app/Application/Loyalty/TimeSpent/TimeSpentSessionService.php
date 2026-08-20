<?php

namespace App\Application\Loyalty\TimeSpent;

use App\Application\Loyalty\Earning\Actions\ProcessEarningRuleAction;
use App\Application\Loyalty\Earning\Contexts\TimeSpentEarningContext;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Enums\LoyaltyTimeSpentStatusEnum;
use App\Domain\Loyalty\Models\EarningRule;
use App\Domain\Loyalty\Models\LoyaltyTimeSpentSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class TimeSpentSessionService
{
    public function __construct(
        private readonly ProcessEarningRuleAction $processEarningRuleAction,
    ) {
    }

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

            $duration = $session->started_at->diffInSeconds(now());

            $session->duration_seconds = $duration;
            $session->completed_at = now();

            $requiredSeconds = (int) data_get(
                $session->earningRule?->config,
                'required_seconds',
                0
            );

            if ($duration < $requiredSeconds) {
                $session->status = LoyaltyTimeSpentStatusEnum::REJECTED;
                $session->save();

                return $session;
            }

            $this->processEarningRuleAction->execute(
                EarningRuleTypeEnum::TIME_SPENT,
                new TimeSpentEarningContext(
                    user: $user,
                    targetType: $session->target_type,
                    targetId: $session->target_id,
                    durationSeconds: $duration,
                    occurredAt: now(),
                )
            );

            $session->status = LoyaltyTimeSpentStatusEnum::COMPLETED;
            $session->save();

            return $session;
        });
    }
}

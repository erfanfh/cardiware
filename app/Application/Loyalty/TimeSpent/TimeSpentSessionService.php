<?php

namespace App\Application\Loyalty\TimeSpent;

use App\Application\Loyalty\Earning\Actions\ProcessEarningRuleAction;
use App\Application\Loyalty\Earning\Contexts\TimeSpentEarningContext;
use App\Application\Loyalty\TimeSpent\DTOs\TimeSpentCompletionResult;
use App\Domain\Identity\Models\User;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use App\Domain\Loyalty\Enums\LoyaltyTimeSpentStatusEnum;
use App\Domain\Loyalty\Models\EarningRule;
use App\Domain\Loyalty\Models\LoyaltyTimeSpentSession;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TimeSpentSessionService
{
    public function __construct(
        private readonly ProcessEarningRuleAction $processEarningRuleAction,
        private readonly TimeSpentRuleEvaluator $evaluator,
    ) {}

    public function start(
        User $user,
        string $targetType,
        int $targetId,
        EarningRule $rule,
    ): LoyaltyTimeSpentSession {
        return LoyaltyTimeSpentSession::firstOrCreate(
            [
                'user_id' => $user->id,
                'earning_rule_id' => $rule->id,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'status' => LoyaltyTimeSpentStatusEnum::PENDING,
            ],
            [
                'token' => Str::uuid()->toString(),
                'started_at' => now(),
            ]
        );
    }

    public function complete(User $user, string $token): TimeSpentCompletionResult
    {
        return DB::transaction(function () use ($user, $token): TimeSpentCompletionResult {
            $session = LoyaltyTimeSpentSession::query()
                ->with('earningRule')
                ->where('user_id', $user->id)
                ->where('token', $token)
                ->lockForUpdate()
                ->firstOrFail();

            if ($session->status !== LoyaltyTimeSpentStatusEnum::PENDING) {
                return new TimeSpentCompletionResult(
                    session: $session,
                );
            }

            $duration = $session->started_at->diffInSeconds(now());

            $session->forceFill([
                'duration_seconds' => $duration,
                'completed_at' => now(),
            ])->save();

            $rule = $session->earningRule;

            if (! $rule) {
                $session->forceFill([
                    'status' => LoyaltyTimeSpentStatusEnum::REJECTED,
                    'rejection_reason' => 'earning_rule_missing',
                ])->save();

                return new TimeSpentCompletionResult(
                    session: $session->refresh(),
                );
            }

            $reason = $this->evaluator->evaluate(
                $user,
                $session,
                $rule,
            );

            if ($reason) {
                $session->forceFill([
                    'status' => LoyaltyTimeSpentStatusEnum::REJECTED,
                    'rejection_reason' => $reason,
                ])->save();

                return new TimeSpentCompletionResult(
                    session: $session->refresh(),
                );
            }

            $processResult = $this->processEarningRuleAction->execute(
                EarningRuleTypeEnum::TIME_SPENT,
                new TimeSpentEarningContext(
                    user: $user,
                    sessionId: (int) $session->getKey(),
                    targetType: $session->target_type,
                    targetId: (int) $session->target_id,
                    durationSeconds: $duration,
                    occurredAt: now(),
                ),
            );

            if (! $processResult->entry) {
                $session->forceFill([
                    'status' => LoyaltyTimeSpentStatusEnum::REJECTED,
                    'rejection_reason' => $processResult->evaluation->skipReason?->value
                        ?? 'earning_rule_not_awarded',
                ])->save();

                return new TimeSpentCompletionResult(
                    session: $session->refresh(),
                );
            }

            $session->forceFill([
                'status' => LoyaltyTimeSpentStatusEnum::COMPLETED,
                'rejection_reason' => null,
            ])->save();

            return new TimeSpentCompletionResult(
                session: $session->refresh(),
                earnedPoints: $processResult->wasNewlyAwarded()
                    ? (int) $processResult->entry->amount
                    : 0,
                entry: $processResult->wasNewlyAwarded()
                    ? $processResult->entry
                    : null,
            );
        }, 3);
    }
}

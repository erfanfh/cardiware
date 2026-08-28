<?php

namespace App\Application\Raffle\Actions\Winner;

use App\Application\Raffle\Exceptions\DrawWinnerTransitionException;
use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Events\DrawWinnerRejectedEvent;
use App\Domain\Raffle\Models\DrawWinner;
use Illuminate\Support\Facades\DB;
use Throwable;

class RejectDrawWinnerAction
{
    public function __construct(private readonly PromoteReserveWinnerAction $promoteReserve)
    {
    }

    /** @return array{winner:DrawWinner,replacement:?DrawWinner} @throws Throwable */
    public function execute(DrawWinner $drawWinner, string $reason, ?User $actor = null): array
    {
        $winner = DB::transaction(function () use ($drawWinner, $reason, $actor): DrawWinner {
            $winner = DrawWinner::query()->whereKey($drawWinner->getKey())->lockForUpdate()->firstOrFail();

            if (!$winner->isEffectivePrimary()) {
                throw new DrawWinnerTransitionException('Only an effective primary winner can be rejected.');
            }
            if (!in_array($winner->status, [DrawWinnerStatusEnum::PENDING, DrawWinnerStatusEnum::CONFIRMED], true)) {
                throw new DrawWinnerTransitionException('Only a pending or confirmed winner can be rejected.');
            }
            if ($winner->hasReplacement()) {
                throw new DrawWinnerTransitionException('This winner has already been replaced.');
            }

            $reason = trim($reason);
            if ($reason === '') {
                throw new DrawWinnerTransitionException('Winner rejection reason is required.');
            }

            $now = now()->toImmutable();
            $winner->forceFill([
                'status' => DrawWinnerStatusEnum::REJECTED,
                'rejected_at' => $now,
                'rejected_by_user_id' => $actor?->getKey(),
                'rejection_reason' => $reason,
            ])->save();

            DB::afterCommit(fn() => event(new DrawWinnerRejectedEvent(
                campaignId: (int)$winner->campaign_id,
                winnerId: (int)$winner->getKey(),
                userId: (int)$winner->user_id,
                reason: $reason,
                actorUserId: $actor?->getKey(),
                occurredAt: $now,
            )));

            return $winner->refresh();
        }, 3);

        $replacement = $this->promoteReserve->execute($winner, $actor);

        return ['winner' => $winner->refresh(), 'replacement' => $replacement];
    }
}

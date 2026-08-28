<?php

namespace App\Application\Raffle\Actions\Winner;

use App\Application\Raffle\Exceptions\DrawWinnerTransitionException;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Events\DrawWinnerExpiredEvent;
use App\Domain\Raffle\Models\DrawWinner;
use Illuminate\Support\Facades\DB;
use Throwable;

class ExpireDrawWinnerAction
{
    public function __construct(private readonly PromoteReserveWinnerAction $promoteReserve)
    {
    }

    /** @return array{winner:DrawWinner,replacement:?DrawWinner} @throws Throwable */
    public function execute(DrawWinner $drawWinner): array
    {
        $winner = DB::transaction(function () use ($drawWinner): DrawWinner {
            $winner = DrawWinner::query()->whereKey($drawWinner->getKey())->lockForUpdate()->firstOrFail();

            if (!$winner->isEffectivePrimary() || $winner->status !== DrawWinnerStatusEnum::PENDING) {
                throw new DrawWinnerTransitionException('Only a pending effective winner can expire.');
            }
            if (!$winner->confirmation_deadline_at || $winner->confirmation_deadline_at->isFuture()) {
                throw new DrawWinnerTransitionException('Winner confirmation deadline has not expired yet.');
            }
            if ($winner->hasReplacement()) {
                return $winner;
            }

            $now = now()->toImmutable();
            $winner->forceFill([
                'status' => DrawWinnerStatusEnum::EXPIRED,
                'expired_at' => $now,
            ])->save();

            DB::afterCommit(fn() => event(new DrawWinnerExpiredEvent(
                campaignId: (int)$winner->campaign_id,
                winnerId: (int)$winner->getKey(),
                userId: (int)$winner->user_id,
                occurredAt: $now,
            )));

            return $winner->refresh();
        }, 3);

        $replacement = $this->promoteReserve->execute($winner);

        return ['winner' => $winner->refresh(), 'replacement' => $replacement];
    }
}

<?php

namespace App\Application\Raffle\Actions\Winner;

use App\Application\Raffle\Exceptions\DrawWinnerTransitionException;
use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Events\DrawPrizeDeliveredEvent;
use App\Domain\Raffle\Models\DrawWinner;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeliverDrawPrizeAction
{
    /** @throws Throwable */
    public function execute(DrawWinner $drawWinner, ?string $note = null, ?User $actor = null): DrawWinner
    {
        return DB::transaction(function () use ($drawWinner, $note, $actor): DrawWinner {
            $winner = DrawWinner::query()->whereKey($drawWinner->getKey())->lockForUpdate()->firstOrFail();

            if (!$winner->isEffectivePrimary() || $winner->status !== DrawWinnerStatusEnum::CONFIRMED) {
                throw new DrawWinnerTransitionException('Only a confirmed effective winner can receive a prize.');
            }
            if ($winner->hasReplacement()) {
                throw new DrawWinnerTransitionException('This winner has already been replaced.');
            }

            $now = now()->toImmutable();
            $winner->forceFill([
                'status' => DrawWinnerStatusEnum::DELIVERED,
                'delivered_at' => $now,
                'delivered_by_user_id' => $actor?->getKey(),
                'delivery_note' => $note ? trim($note) : null,
            ])->save();

            DB::afterCommit(fn() => event(new DrawPrizeDeliveredEvent(
                campaignId: (int)$winner->campaign_id,
                winnerId: (int)$winner->getKey(),
                prizeId: (int)$winner->prize_id,
                userId: (int)$winner->user_id,
                actorUserId: $actor?->getKey(),
                occurredAt: $now,
            )));

            return $winner->refresh();
        }, 3);
    }
}

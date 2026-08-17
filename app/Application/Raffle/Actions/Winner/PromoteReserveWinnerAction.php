<?php

namespace App\Application\Raffle\Actions\Winner;

use App\Application\Raffle\Exceptions\DrawWinnerTransitionException;
use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Enums\DrawWinnerKindEnum;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Events\DrawReservePromotedEvent;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawWinner;
use Illuminate\Support\Facades\DB;
use Throwable;

class PromoteReserveWinnerAction
{
    /** @throws Throwable */
    public function execute(DrawWinner $failedWinner, ?User $actor = null): ?DrawWinner
    {
        return DB::transaction(function () use ($failedWinner, $actor): ?DrawWinner {
            $failed = DrawWinner::query()
                ->whereKey($failedWinner->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $campaign = DrawCampaign::query()
                ->whereKey($failed->campaign_id)
                ->lockForUpdate()
                ->firstOrFail();

            if (!in_array($failed->status, [DrawWinnerStatusEnum::REJECTED, DrawWinnerStatusEnum::EXPIRED], true)) {
                throw new DrawWinnerTransitionException('Only a rejected or expired effective winner can be replaced.');
            }

            $primaryPosition = $failed->effectivePrimaryPosition();
            if ($primaryPosition === null) {
                throw new DrawWinnerTransitionException('The selected winner does not represent a primary prize position.');
            }

            if (DrawWinner::query()->where('replaces_winner_id', $failed->getKey())->exists()) {
                return DrawWinner::query()->where('replaces_winner_id', $failed->getKey())->first();
            }

            $reservedUserIds = [];
            if (!$campaign->allow_multiple_wins_per_user) {
                $reservedUserIds = DrawWinner::query()
                    ->where('campaign_id', $campaign->getKey())
                    ->where(function ($query): void {
                        $query->where('kind', DrawWinnerKindEnum::PRIMARY)
                            ->orWhereNotNull('promoted_at');
                    })
                    ->whereIn('status', [
                        DrawWinnerStatusEnum::PENDING,
                        DrawWinnerStatusEnum::CONFIRMED,
                        DrawWinnerStatusEnum::DELIVERED,
                    ])
                    ->pluck('user_id')
                    ->map(fn($id) => (int)$id)
                    ->all();
            }

            $reserveQuery = DrawWinner::query()
                ->where('campaign_id', $campaign->getKey())
                ->where('prize_id', $failed->prize_id)
                ->where('kind', DrawWinnerKindEnum::RESERVE)
                ->where('status', DrawWinnerStatusEnum::PENDING)
                ->whereNull('promoted_at')
                ->whereNull('replaces_winner_id')
                ->where('user_id', '!=', $failed->user_id)
                ->orderBy('selected_at')
                ->orderBy('draw_run_id')
                ->orderBy('position')
                ->orderBy('id')
                ->lockForUpdate();

            if ($reservedUserIds !== []) {
                $reserveQuery->whereNotIn('user_id', $reservedUserIds);
            }

            $reserve = $reserveQuery->first();
            if (!$reserve) {
                return null;
            }

            $now = now()->toImmutable();
            $deadline = $campaign->winner_confirmation_window_hours
                ? $now->addHours((int)$campaign->winner_confirmation_window_hours)
                : null;

            $reserve->forceFill([
                'promoted_at' => $now,
                'promoted_by_user_id' => $actor?->getKey(),
                'promoted_for_position' => $primaryPosition,
                'replaces_winner_id' => $failed->getKey(),
                'confirmation_deadline_at' => $deadline,
            ])->save();

            DB::afterCommit(fn() => event(new DrawReservePromotedEvent(
                campaignId: (int)$campaign->getKey(),
                prizeId: (int)$reserve->prize_id,
                winnerId: (int)$reserve->getKey(),
                replacedWinnerId: (int)$failed->getKey(),
                primaryPosition: $primaryPosition,
                actorUserId: $actor?->getKey(),
                occurredAt: $now,
            )));

            return $reserve->refresh();
        }, 3);
    }
}

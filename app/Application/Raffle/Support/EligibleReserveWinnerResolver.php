<?php

namespace App\Application\Raffle\Support;

use App\Domain\Raffle\Enums\DrawWinnerKindEnum;
use App\Domain\Raffle\Enums\DrawWinnerStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawWinner;
use Illuminate\Database\Eloquent\Builder;

class EligibleReserveWinnerResolver
{
    public function query(DrawWinner $failedWinner, DrawCampaign $campaign): Builder
    {
        $query = DrawWinner::query()
            ->where('campaign_id', $campaign->getKey())
            ->where('prize_id', $failedWinner->prize_id)
            ->where('kind', DrawWinnerKindEnum::RESERVE)
            ->where('status', DrawWinnerStatusEnum::PENDING)
            ->whereNull('promoted_at')
            ->whereNull('replaces_winner_id')
            ->where('user_id', '!=', $failedWinner->user_id)
            ->orderBy('selected_at')
            ->orderBy('draw_run_id')
            ->orderBy('position')
            ->orderBy('id');

        if (!$campaign->allow_multiple_wins_per_user) {
            $effectiveWinnerUserIds = DrawWinner::query()
                ->select('user_id')
                ->where('campaign_id', $campaign->getKey())
                ->where(function (Builder $query): void {
                    $query
                        ->where('kind', DrawWinnerKindEnum::PRIMARY)
                        ->orWhereNotNull('promoted_at');
                });

            $query->whereNotIn('user_id', $effectiveWinnerUserIds);
        }

        return $query;
    }

    public function firstForUpdate(DrawWinner $failedWinner, DrawCampaign $campaign): ?DrawWinner
    {
        return $this->query($failedWinner, $campaign)
            ->lockForUpdate()
            ->first();
    }

    public function exists(DrawWinner $failedWinner, DrawCampaign $campaign): bool
    {
        return $this->query($failedWinner, $campaign)->exists();
    }
}

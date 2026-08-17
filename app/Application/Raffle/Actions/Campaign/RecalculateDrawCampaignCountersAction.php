<?php

namespace App\Application\Raffle\Actions\Campaign;

use App\Domain\Raffle\Enums\DrawTicketStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use App\Domain\Raffle\Models\DrawTicket;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class RecalculateDrawCampaignCountersAction
{
    /** @throws Throwable */
    public function execute(DrawCampaign $campaign): DrawCampaign
    {
        if (!$campaign->exists) {
            throw new InvalidArgumentException(
                'Draw campaign must exist before its counters can be recalculated.'
            );
        }

        return DB::transaction(function () use ($campaign): DrawCampaign {
            $campaign = DrawCampaign::query()
                ->whereKey($campaign->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $issuedTicketsCount = DrawTicket::query()
                ->where('campaign_id', $campaign->getKey())
                ->count();

            $activeTicketsQuery = DrawTicket::query()
                ->where('campaign_id', $campaign->getKey())
                ->where('status', DrawTicketStatusEnum::ACTIVE->value);

            $activeTicketsCount = (clone $activeTicketsQuery)->count();
            $participantsCount = (clone $activeTicketsQuery)
                ->distinct()
                ->count('user_id');

            $campaign->forceFill([
                'issued_tickets_count' => $issuedTicketsCount,
                'active_tickets_count' => $activeTicketsCount,
                'participants_count' => $participantsCount,
            ])->save();

            return $campaign->refresh();
        }, 3);
    }
}

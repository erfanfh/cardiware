<?php

namespace App\Application\Raffle\Support;

use App\Application\Raffle\Exceptions\DrawCampaignStatusTransitionException;
use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use App\Domain\Raffle\Models\DrawCampaign;
use Carbon\CarbonInterface;

class DrawCampaignStatusTransitionGuard
{
    public function ensureAllowed(
        DrawCampaign $campaign,
        DrawCampaignStatusEnum $targetStatus,
        CarbonInterface $occurredAt,
    ): void {
        $currentStatus = $campaign->status;

        if (!$currentStatus->canTransitionTo($targetStatus)) {
            throw new DrawCampaignStatusTransitionException(
                "Draw campaign cannot transition from [{$currentStatus->value}] to [{$targetStatus->value}]."
            );
        }

        if ($currentStatus === $targetStatus) {
            return;
        }

        if (in_array($targetStatus, [
            DrawCampaignStatusEnum::SCHEDULED,
            DrawCampaignStatusEnum::OPEN,
        ], true) && !$campaign->prizes()->exists()) {
            throw new DrawCampaignStatusTransitionException(
                'Draw campaign must have at least one prize before it can be published.'
            );
        }

        if ($targetStatus === DrawCampaignStatusEnum::SCHEDULED) {
            if (!$campaign->starts_at || !$campaign->starts_at->gt($occurredAt)) {
                throw new DrawCampaignStatusTransitionException(
                    'A scheduled draw campaign must have a future start date.'
                );
            }
        }

        if ($targetStatus === DrawCampaignStatusEnum::OPEN) {
            if ($campaign->starts_at && $occurredAt->lt($campaign->starts_at)) {
                throw new DrawCampaignStatusTransitionException(
                    'Draw campaign cannot be opened before its configured start date.'
                );
            }

            if ($campaign->closes_at && !$occurredAt->lt($campaign->closes_at)) {
                throw new DrawCampaignStatusTransitionException(
                    'Draw campaign cannot be opened after its configured close date.'
                );
            }
        }

        if ($targetStatus === DrawCampaignStatusEnum::DRAWING) {
            if ($campaign->active_tickets_count <= 0) {
                throw new DrawCampaignStatusTransitionException(
                    'Draw campaign cannot start drawing without eligible active tickets.'
                );
            }

            if (!$campaign->prizes()->exists()) {
                throw new DrawCampaignStatusTransitionException(
                    'Draw campaign cannot start drawing without at least one prize.'
                );
            }
        }
    }
}

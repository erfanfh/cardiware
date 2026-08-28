<?php

namespace App\Domain\Raffle\Events;

use App\Domain\Raffle\Enums\DrawCampaignStatusEnum;
use Carbon\CarbonImmutable;

final readonly class DrawCampaignStatusChangedEvent
{
    public function __construct(
        public int $campaignId,
        public DrawCampaignStatusEnum $from,
        public DrawCampaignStatusEnum $to,
        public ?int $actorUserId,
        public CarbonImmutable $occurredAt,
        public ?string $reason = null,
    ) {
    }
}

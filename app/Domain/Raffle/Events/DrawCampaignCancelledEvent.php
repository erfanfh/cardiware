<?php

namespace App\Domain\Raffle\Events;

use Carbon\CarbonImmutable;

final readonly class DrawCampaignCancelledEvent
{
    public function __construct(
        public int $campaignId,
        public ?int $actorUserId,
        public CarbonImmutable $occurredAt,
        public ?string $reason = null,
    ) {
    }
}

<?php

namespace App\Domain\Raffle\Events;

use Carbon\CarbonImmutable;

final readonly class DrawCampaignCreatedEvent
{
    public function __construct(
        public int $campaignId,
        public ?int $actorUserId,
        public CarbonImmutable $occurredAt,
    ) {}
}

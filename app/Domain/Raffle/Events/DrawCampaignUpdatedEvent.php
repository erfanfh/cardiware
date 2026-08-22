<?php

namespace App\Domain\Raffle\Events;

use Carbon\CarbonImmutable;

final readonly class DrawCampaignUpdatedEvent
{
    public function __construct(
        public int $campaignId,
        public ?int $actorUserId,
        public array $changedFields,
        public CarbonImmutable $occurredAt,
    ) {}
}

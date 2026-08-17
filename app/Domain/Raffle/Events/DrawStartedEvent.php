<?php

namespace App\Domain\Raffle\Events;

use Carbon\CarbonImmutable;

final readonly class DrawStartedEvent
{
    public function __construct(
        public int $campaignId,
        public int $drawRunId,
        public ?int $actorUserId,
        public string $seedCommitment,
        public CarbonImmutable $occurredAt,
    ) {
    }
}

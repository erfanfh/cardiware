<?php

namespace App\Domain\Raffle\Events;

use Carbon\CarbonImmutable;

final readonly class DrawRedrawStartedEvent
{
    public function __construct(
        public int $campaignId,
        public int $drawRunId,
        public int $failedWinnerId,
        public int $prizeId,
        public int $primaryPosition,
        public ?int $actorUserId,
        public string $seedCommitment,
        public CarbonImmutable $occurredAt,
    ) {
    }
}

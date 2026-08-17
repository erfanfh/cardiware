<?php

namespace App\Domain\Raffle\Events;

use Carbon\CarbonImmutable;

final readonly class DrawCompletedEvent
{
    public function __construct(
        public int $campaignId,
        public int $drawRunId,
        public int $eligibleTicketCount,
        public string $eligibleSetHash,
        public CarbonImmutable $occurredAt,
    ) {
    }
}

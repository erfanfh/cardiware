<?php

namespace App\Domain\Raffle\Events;

use Carbon\CarbonImmutable;

final readonly class DrawFailedEvent
{
    public function __construct(
        public int $campaignId,
        public int $drawRunId,
        public string $reason,
        public CarbonImmutable $occurredAt,
    ) {
    }
}

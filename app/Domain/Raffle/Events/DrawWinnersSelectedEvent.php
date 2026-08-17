<?php

namespace App\Domain\Raffle\Events;

use Carbon\CarbonImmutable;

final readonly class DrawWinnersSelectedEvent
{
    /** @param list<int> $winnerIds */
    public function __construct(
        public int $campaignId,
        public int $drawRunId,
        public array $winnerIds,
        public CarbonImmutable $occurredAt,
    ) {
    }
}

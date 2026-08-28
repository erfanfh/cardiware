<?php

namespace App\Domain\Raffle\Events;

use Carbon\CarbonImmutable;

final readonly class DrawWinnerExpiredEvent
{
    public function __construct(
        public int $campaignId,
        public int $winnerId,
        public int $userId,
        public CarbonImmutable $occurredAt,
    ) {
    }
}

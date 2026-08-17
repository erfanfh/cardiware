<?php

namespace App\Domain\Raffle\Events;

use Carbon\CarbonImmutable;

final readonly class DrawWinnerRejectedEvent
{
    public function __construct(
        public int $campaignId,
        public int $winnerId,
        public int $userId,
        public string $reason,
        public ?int $actorUserId,
        public CarbonImmutable $occurredAt,
    ) {
    }
}

<?php

namespace App\Domain\Raffle\Events;

use Carbon\CarbonImmutable;

final readonly class DrawPrizeDeliveredEvent
{
    public function __construct(
        public int $campaignId,
        public int $winnerId,
        public int $prizeId,
        public int $userId,
        public ?int $actorUserId,
        public CarbonImmutable $occurredAt,
    ) {
    }
}

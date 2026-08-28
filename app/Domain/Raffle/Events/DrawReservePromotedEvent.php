<?php

namespace App\Domain\Raffle\Events;

use Carbon\CarbonImmutable;

final readonly class DrawReservePromotedEvent
{
    public function __construct(
        public int $campaignId,
        public int $prizeId,
        public int $winnerId,
        public int $replacedWinnerId,
        public int $primaryPosition,
        public ?int $actorUserId,
        public CarbonImmutable $occurredAt,
    ) {
    }
}

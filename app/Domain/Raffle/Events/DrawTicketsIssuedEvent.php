<?php

namespace App\Domain\Raffle\Events;

use Carbon\CarbonImmutable;

final readonly class DrawTicketsIssuedEvent
{
    public function __construct(
        public int $campaignId,
        public int $ticketGrantId,
        public int $userId,
        public int $quantity,
        public CarbonImmutable $occurredAt,
    ) {
    }
}

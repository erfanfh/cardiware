<?php

namespace App\Domain\Raffle\Events;

use Carbon\CarbonImmutable;

final readonly class DrawTicketsInvalidatedEvent
{
    /**
     * ticketIds is populated for targeted invalidations. For a whole-grant
     * invalidation, ticketGrantId + quantity identify the affected batch without
     * putting a potentially huge ticket-id array on the event payload.
     *
     * @param list<int> $ticketIds
     */
    public function __construct(
        public int $campaignId,
        public int $userId,
        public array $ticketIds,
        public int $quantity,
        public string $reason,
        public ?int $actorUserId,
        public CarbonImmutable $occurredAt,
        public ?int $ticketGrantId = null,
    ) {
    }
}

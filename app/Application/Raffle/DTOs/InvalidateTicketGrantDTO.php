<?php

namespace App\Application\Raffle\DTOs;

use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Models\TicketGrant;
use Carbon\CarbonImmutable;

final readonly class InvalidateTicketGrantDTO
{
    public function __construct(
        public TicketGrant $grant,
        public string $reason,
        public ?User $invalidatedBy = null,
        public ?CarbonImmutable $occurredAt = null,
    ) {
    }
}

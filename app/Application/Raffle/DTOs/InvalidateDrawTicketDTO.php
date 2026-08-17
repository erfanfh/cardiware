<?php

namespace App\Application\Raffle\DTOs;

use App\Domain\Identity\Models\User;
use App\Domain\Raffle\Models\DrawTicket;
use Carbon\CarbonImmutable;

final readonly class InvalidateDrawTicketDTO
{
    public function __construct(
        public DrawTicket $ticket,
        public string $reason,
        public ?User $invalidatedBy = null,
        public ?CarbonImmutable $occurredAt = null,
    ) {
    }
}

<?php

namespace App\Domain\Identity\Events;

use Carbon\CarbonImmutable;

final readonly class UserRegisteredEvent
{
    public function __construct(
        public int $userId,
        public CarbonImmutable $occurredAt,
    ) {
    }
}

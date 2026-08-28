<?php

namespace App\Domain\Discussion\Events;

use Carbon\CarbonImmutable;

final readonly class CommentDisqualifiedEvent
{
    public function __construct(
        public int $commentId,
        public string $reason,
        public CarbonImmutable $occurredAt,
    ) {
    }
}

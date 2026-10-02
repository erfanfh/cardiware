<?php

namespace App\Domain\Discussion\Events;

use Carbon\CarbonImmutable;

final readonly class CommentApprovedEvent
{
    public function __construct(
        public int $commentId,
        public CarbonImmutable $occurredAt,
        public bool $approvedOnCreation = false,
    ) {
    }
}

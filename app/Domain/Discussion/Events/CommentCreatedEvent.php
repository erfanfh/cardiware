<?php

namespace App\Domain\Discussion\Events;

final readonly class CommentCreatedEvent
{
    public function __construct(public int $commentId) {}
}

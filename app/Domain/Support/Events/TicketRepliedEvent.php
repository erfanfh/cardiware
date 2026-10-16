<?php

namespace App\Domain\Support\Events;

final readonly class TicketRepliedEvent
{
    public function __construct(public int $messageId) {}
}

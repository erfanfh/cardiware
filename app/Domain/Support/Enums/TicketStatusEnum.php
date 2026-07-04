<?php

namespace App\Domain\Support\Enums;

enum TicketStatusEnum: string
{
    case OPEN = 'open';
    case ANSWERED = 'answered';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::OPEN => 'باز',
            self::ANSWERED => 'پاسخ داده شده',
            self::CLOSED => 'بسته شده',
        };
    }

    public function isOpen(): bool
    {
        return $this !== self::CLOSED;
    }

    public function isClosed(): bool
    {
        return $this === self::CLOSED;
    }

    public function canReceiveReply(): bool
    {
        return !$this->isClosed();
    }
}

<?php

namespace App\Domain\Raffle\Enums;

enum DrawTicketStatusEnum: string
{
    case ACTIVE = 'active';
    case INVALIDATED = 'invalidated';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'فعال',
            self::INVALIDATED => 'باطل شده',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}

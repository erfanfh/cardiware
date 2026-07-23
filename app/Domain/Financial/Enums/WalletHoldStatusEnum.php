<?php

namespace App\Domain\Financial\Enums;

enum WalletHoldStatusEnum: string
{
    case ACTIVE = 'active';
    case CAPTURED = 'captured';
    case RELEASED = 'released';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'فعال',
            self::CAPTURED => 'برداشت شده',
            self::RELEASED => 'آزاد شده',
            self::EXPIRED => 'منقضی شده',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }

    public function isCaptured(): bool
    {
        return $this === self::CAPTURED;
    }

    public function isReleased(): bool
    {
        return $this === self::RELEASED;
    }

    public function isExpired(): bool
    {
        return $this === self::EXPIRED;
    }

    public function isTerminal(): bool
    {
        return in_array($this, [
            self::CAPTURED,
            self::RELEASED,
            self::EXPIRED,
        ], true);
    }
}

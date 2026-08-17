<?php

namespace App\Domain\Raffle\Enums;

enum DrawRunStatusEnum: string
{
    case PENDING = 'pending';
    case RUNNING = 'running';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'در انتظار اجرا',
            self::RUNNING => 'در حال اجرا',
            self::COMPLETED => 'تکمیل شده',
            self::FAILED => 'ناموفق',
        };
    }

    public function isTerminal(): bool
    {
        return $this === self::COMPLETED;
    }

    public function canTransitionTo(self $status): bool
    {
        if ($this === $status) {
            return true;
        }

        return match ($this) {
            self::PENDING => in_array($status, [self::RUNNING, self::FAILED], true),
            self::RUNNING => in_array($status, [self::COMPLETED, self::FAILED], true),
            self::FAILED => $status === self::RUNNING,
            self::COMPLETED => false,
        };
    }
}

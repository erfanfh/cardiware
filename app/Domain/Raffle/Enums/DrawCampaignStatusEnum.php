<?php

namespace App\Domain\Raffle\Enums;

enum DrawCampaignStatusEnum: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case OPEN = 'open';
    case CLOSED = 'closed';
    case DRAWING = 'drawing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'پیش نویس',
            self::SCHEDULED => 'زمان بندی شده',
            self::OPEN => 'در حال برگزاری',
            self::CLOSED => 'بسته شده',
            self::DRAWING => 'در حال قرعه کشی',
            self::COMPLETED => 'تکمیل شده',
            self::CANCELLED => 'لغو شده',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED], true);
    }

    public function acceptsTicketIssuance(): bool
    {
        return $this === self::OPEN;
    }

    public function allowsTicketInvalidation(): bool
    {
        return in_array($this, [self::OPEN, self::CLOSED], true);
    }

    public function canTransitionTo(self $status): bool
    {
        if ($this === $status) {
            return true;
        }

        return match ($this) {
            self::DRAFT => in_array($status, [
                self::SCHEDULED,
                self::OPEN,
                self::CANCELLED,
            ], true),

            self::SCHEDULED => in_array($status, [
                self::OPEN,
                self::CANCELLED,
            ], true),

            self::OPEN => in_array($status, [
                self::CLOSED,
                self::CANCELLED,
            ], true),

            self::CLOSED => in_array($status, [
                self::DRAWING,
                self::CANCELLED,
            ], true),

            self::DRAWING => $status === self::COMPLETED,
            self::COMPLETED,
            self::CANCELLED => false,
        };
    }
}

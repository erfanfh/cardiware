<?php

namespace App\Domain\Support\Enums;

enum TicketMessageSenderTypeEnum: string
{
    case USER = 'user';
    case SUPPORT = 'support';
    case SYSTEM = 'system';

    public function label(): string
    {
        return match ($this) {
            self::USER => 'کاربر',
            self::SUPPORT => 'پشتیبانی',
            self::SYSTEM => 'سیستم',
        };
    }

    public function isUser(): bool
    {
        return $this === self::USER;
    }

    public function isSupport(): bool
    {
        return $this === self::SUPPORT;
    }

    public function isSystem(): bool
    {
        return $this === self::SYSTEM;
    }
}

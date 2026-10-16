<?php

namespace App\Domain\Notification\Enums;

enum CustomerSmsType: string
{
    case TICKET_REPLY = 'ticket_reply';
    case ORDER_PAID = 'order_paid';

    public function label(): string
    {
        return match ($this) {
            self::TICKET_REPLY => 'پیامک پاسخ پشتیبانی به تیکت',
            self::ORDER_PAID => 'پیامک تأیید خرید موفق',
        };
    }

    public function settingKey(): string
    {
        return $this->value.'_sms_enabled';
    }

    /** @return array<string, string> */
    public function parameters(): array
    {
        return match ($this) {
            self::TICKET_REPLY => ['name' => 'Name', 'ticket_number' => 'TicketNumber'],
            self::ORDER_PAID => ['name' => 'Name', 'order_number' => 'OrderNumber'],
        };
    }
}

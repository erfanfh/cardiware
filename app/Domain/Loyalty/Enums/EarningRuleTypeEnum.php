<?php

namespace App\Domain\Loyalty\Enums;

enum EarningRuleTypeEnum: string
{
    case REGISTRATION = 'registration';
    case DAILY_VISIT = 'daily_visit';
    case PURCHASE = 'purchase';
    case COMMENT = 'comment';
    case MEMBERSHIP_ANNIVERSARY = 'membership_anniversary';

    public function label(): string
    {
        return match ($this) {
            self::REGISTRATION => 'ثبت نام',
            self::DAILY_VISIT => 'بازدید روزانه',
            self::PURCHASE => 'خرید',
            self::COMMENT => 'ثبت دیدگاه',
            self::MEMBERSHIP_ANNIVERSARY => 'سالگرد عضویت',
        };
    }
}

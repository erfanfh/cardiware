<?php

namespace App\Domain\Poll\Enums;

enum PollAudienceEnum: string
{
    case EVERYONE = 'everyone';
    case MEMBERS = 'members';
    // فقط اعضای «کانون نخبگان» باشگاه (سطح تنظیم‌شده به بالا)؛ همه می‌بینند، آن‌ها رأی می‌دهند.
    case CLUB_ELITE = 'club_elite';

    public function label(): string
    {
        return match ($this) {
            self::EVERYONE => 'همه کاربران',
            self::MEMBERS => 'فقط اعضای واردشده',
            self::CLUB_ELITE => 'فقط کانون نخبگان باشگاه',
        };
    }
}

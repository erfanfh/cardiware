<?php

namespace App\Domain\Poll\Enums;

enum PollAudienceEnum: string
{
    case EVERYONE = 'everyone';
    case MEMBERS = 'members';

    public function label(): string
    {
        return match ($this) {
            self::EVERYONE => 'همه کاربران',
            self::MEMBERS => 'فقط اعضای واردشده',
        };
    }
}

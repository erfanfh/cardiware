<?php

namespace App\Domain\Poll\Enums;

enum PollResultsVisibilityEnum: string
{
    case ALWAYS = 'always';
    case AFTER_VOTE = 'after_vote';
    case AFTER_END = 'after_end';
    case ADMIN_ONLY = 'admin_only';

    public function label(): string
    {
        return match ($this) {
            self::ALWAYS => 'همیشه',
            self::AFTER_VOTE => 'پس از ثبت رأی',
            self::AFTER_END => 'پس از پایان نظرسنجی',
            self::ADMIN_ONLY => 'فقط مدیران',
        };
    }
}

<?php

namespace App\Domain\Project\Enums;

enum ProjectSortEnum: string
{
    case MOST_PROGRESS = 'most-progress';
    case LEAST_PROGRESS = 'least-progress';
    case LATER_UPDATE = 'later-update';
    case OLDER_UPDATE = 'older-update';

    public function label(): string
    {
        return match ($this) {
            self::MOST_PROGRESS => 'پیشرفت (بیشتر)',
            self::LEAST_PROGRESS => 'پیشرفت (کمتر)',
            self::LATER_UPDATE => 'آپدیت (جدیدتر)',
            self::OLDER_UPDATE => 'آپدیت (قدیمی‌تر)',
        };
    }
}

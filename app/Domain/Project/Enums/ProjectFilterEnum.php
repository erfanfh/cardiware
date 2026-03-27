<?php

namespace App\Domain\Project\Enums;

enum ProjectFilterEnum: string
{
    case ALL = 'all';
    case NOT_STARTED = 'not-started';
    case INPROGRESS = 'inprogress';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::ALL => 'همه',
            self::NOT_STARTED => 'شروع نشده',
            self::INPROGRESS => 'در حال تکمیل',
            self::COMPLETED => 'تکمیل',
        };
    }
}

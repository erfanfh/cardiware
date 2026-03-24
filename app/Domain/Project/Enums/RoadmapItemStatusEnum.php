<?php

namespace App\Domain\Project\Enums;

enum RoadmapItemStatusEnum: string
{
    case WAITING = 'waiting';
    case INPROGRESS = 'inprogress';
    case DONE = 'done';

    public function label(): string
    {
        return match ($this) {
            self::WAITING => 'انتظار',
            self::INPROGRESS => 'در حال انجام',
            self::DONE => 'تکمیل',
        };
    }
}

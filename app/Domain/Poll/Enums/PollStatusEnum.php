<?php

namespace App\Domain\Poll\Enums;

enum PollStatusEnum: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'پیش‌نویس',
            self::PUBLISHED => 'منتشر شده',
            self::ARCHIVED => 'بایگانی شده',
        };
    }
}

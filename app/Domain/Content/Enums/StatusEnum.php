<?php

namespace App\Domain\Content\Enums;

enum StatusEnum: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case PENDING_REVIEW = 'pending_review';

    public function label()
    {
        return match ($this) {
            self::DRAFT => 'پیش نویس',
            self::PUBLISHED => 'انتشار یافته',
            self::PENDING_REVIEW => 'در انتشار انتشار',
        };
    }
}

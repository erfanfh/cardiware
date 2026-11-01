<?php

namespace App\Domain\Content\Enums;

enum UserMediaSubmissionTypeEnum: string
{
    case IMAGE = 'image';
    case VIDEO = 'video';

    public function label(): string
    {
        return match ($this) {
            self::IMAGE => 'تصویر',
            self::VIDEO => 'ویدیو',
        };
    }
}

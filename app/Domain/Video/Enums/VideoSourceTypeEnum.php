<?php

namespace App\Domain\Video\Enums;

enum VideoSourceTypeEnum: string
{
    case APARAT = 'aparat';
    case DIRECT = 'direct';

    public function label(): string
    {
        return match ($this) {
            self::APARAT => 'آپارات',
            self::DIRECT => 'لینک مستقیم',
        };
    }
}

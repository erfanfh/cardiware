<?php

namespace App\Domain\Media\Enums;

enum MediaFileVariantEnum: string
{
    case ORIGINAL = 'original';
    case THUMBNAIL = 'thumbnail';
}

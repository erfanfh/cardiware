<?php

namespace App\Domain\Media\Enums;

enum MediaFileVariantEnum: string
{
    case ORIGINAL = 'original';
    case THUMBNAIL = 'thumbnail';
    case MEDIUM = 'medium';
    case MEDIUM_LARGE = 'medium_large';
    case LARGE = 'large';
}

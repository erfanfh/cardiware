<?php

namespace App\Domain\Media\Enums;

enum MediaFileVisibilityEnum: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
}

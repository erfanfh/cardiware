<?php

namespace App\Domain\Media\Enums;

enum MediaFileTypeEnum: string
{
    case IMAGE = 'image';
    case DOCUMENT = 'document';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case OTHER = 'other';
}

<?php

namespace App\Domain\Support\Enums;

enum TicketAttachmentVariantEnum: string
{
    case ORIGINAL = 'original';
    case THUMBNAIL = 'thumbnail';
    case MEDIUM = 'medium';
    case MEDIUM_LARGE = 'medium_large';
    case LARGE = 'large';
}

<?php

namespace App\Domain\Media\Enums;

enum MediaFileStatusEnum: string
{
    case PROCESSING = 'processing';
    case READY = 'ready';
    case FAILED = 'failed';
}

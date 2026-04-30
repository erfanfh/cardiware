<?php

namespace App\Domain\Media\Enums;

enum MediaFileDiskEnum: string
{
    case PUBLIC = 'public';
    case WORDPRESS_UPLOADS = 'wordpress_uploads';
}

<?php

namespace App\Domain\Download\Enums;

enum DownloadEntryTypeEnum: string
{
    case LINK = 'link';
    case TEXT = 'text';
}

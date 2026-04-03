<?php

namespace App\Domain\Download\Enums;

enum DownloadEventTypeEnum: string
{
    case ENTRY_CLICKED = 'entry_clicked';
    case SECTION_LINKS_COPIED = 'section_links_copied';
}

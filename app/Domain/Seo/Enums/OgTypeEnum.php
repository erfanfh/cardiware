<?php

namespace App\Domain\Seo\Enums;

enum OgTypeEnum: string
{
    case WEBSITE = 'website';
    case ARTICLE = 'article';
    case PROFILE = 'profile';
    case VIDEO = 'video.other';
}

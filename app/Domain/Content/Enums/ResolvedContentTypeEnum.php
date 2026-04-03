<?php

namespace App\Domain\Content\Enums;

enum ResolvedContentTypeEnum: string
{
    case NOT_FOUND = 'not_found';
    case REDIRECT = 'redirect';
    case PAGE = 'page';
    case POST = 'post';
}

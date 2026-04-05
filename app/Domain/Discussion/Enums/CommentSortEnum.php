<?php

namespace App\Domain\Discussion\Enums;

enum CommentSortEnum: string
{
    case NEWEST = 'newest';
    case OLDEST = 'oldest';
    case BEST = 'best';
}

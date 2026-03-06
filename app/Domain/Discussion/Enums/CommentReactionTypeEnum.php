<?php

namespace App\Domain\Discussion\Enums;

enum CommentReactionTypeEnum: string
{
    case LIKE = 'like';
    case DISLIKE = 'dislike';
}

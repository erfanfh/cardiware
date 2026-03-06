<?php

namespace App\Domain\Discussion\Enums;

enum CommentStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}

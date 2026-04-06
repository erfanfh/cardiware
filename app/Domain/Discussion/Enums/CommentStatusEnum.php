<?php

namespace App\Domain\Discussion\Enums;

enum CommentStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function isApproved(): bool
    {
        return $this === self::APPROVED;
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }
}

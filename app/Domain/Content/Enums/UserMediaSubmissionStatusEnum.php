<?php

namespace App\Domain\Content\Enums;

enum UserMediaSubmissionStatusEnum: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'در انتظار تأیید',
            self::APPROVED => 'تأییدشده',
            self::REJECTED => 'ردشده',
        };
    }
}

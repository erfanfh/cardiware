<?php

namespace App\Domain\Commerce\Enums;

enum PurchaseAccessDownloadStatusEnum: string
{
    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    /**
     * Get the human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'در حال دانلود',
            self::COMPLETED => 'تکمیل شده',
            self::FAILED => 'ناموفق',
        };
    }
}

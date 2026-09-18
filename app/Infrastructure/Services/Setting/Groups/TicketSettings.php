<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\IntegerCast;
use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class TicketSettings extends BaseSettings
{
    public const DEFAULT_ATTACHMENT_MAX_SIZE_MB = 5;

    public const DEFAULT_ATTACHMENTS_PER_MESSAGE = 4;

    public const DEFAULT_ATTACHMENT_RETENTION_DAYS = 90;

    protected string $group = 'ticket';

    protected array $allowedKeys = [
        'ticket_submission_tips',
        'attachment_max_size_mb',
        'attachments_per_message',
        'attachment_retention_days',
    ];

    protected array $casts = [
        'ticket_submission_tips' => RepeaterArrayCast::class,
        'attachment_max_size_mb' => IntegerCast::class,
        'attachments_per_message' => IntegerCast::class,
        'attachment_retention_days' => IntegerCast::class,
    ];

    public function attachmentMaxSizeMegabytes(): int
    {
        return min(20, max(1, (int) $this->get(
            'attachment_max_size_mb',
            self::DEFAULT_ATTACHMENT_MAX_SIZE_MB,
        )));
    }

    public function attachmentsPerMessage(): int
    {
        return min(10, max(1, (int) $this->get(
            'attachments_per_message',
            self::DEFAULT_ATTACHMENTS_PER_MESSAGE,
        )));
    }

    public function attachmentRetentionDays(): int
    {
        return min(3650, max(1, (int) $this->get(
            'attachment_retention_days',
            self::DEFAULT_ATTACHMENT_RETENTION_DAYS,
        )));
    }
}

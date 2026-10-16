<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class NotificationsSettings extends BaseSettings
{
    protected string $group = 'notifications';

    protected array $allowedKeys = [
        'ticket_reply_sms_enabled',
        'order_paid_sms_enabled',
    ];

    protected array $casts = [
        'ticket_reply_sms_enabled' => BooleanCast::class,
        'order_paid_sms_enabled' => BooleanCast::class,
    ];
}

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
        'recent_purchases_enabled',
    ];

    protected array $casts = [
        'ticket_reply_sms_enabled' => BooleanCast::class,
        'order_paid_sms_enabled' => BooleanCast::class,
        'recent_purchases_enabled' => BooleanCast::class,
    ];

    public function recentPurchasesEnabled(): bool
    {
        return (bool) $this->get('recent_purchases_enabled', true);
    }
}

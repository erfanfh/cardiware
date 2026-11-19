<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Casts\IntegerCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class NotificationsSettings extends BaseSettings
{
    protected string $group = 'notifications';

    protected array $allowedKeys = [
        'ticket_reply_sms_enabled',
        'order_paid_sms_enabled',
        'recent_purchases_enabled',
        'cleanup_days',
    ];

    protected array $casts = [
        'ticket_reply_sms_enabled' => BooleanCast::class,
        'order_paid_sms_enabled' => BooleanCast::class,
        'recent_purchases_enabled' => BooleanCast::class,
        'cleanup_days' => IntegerCast::class,
    ];

    public function recentPurchasesEnabled(): bool
    {
        return (bool) $this->get('recent_purchases_enabled', true);
    }
}

<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\IntegerCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class CommerceSettings extends BaseSettings
{
    protected string $group = 'commerce';

    protected array $allowedKeys = [
        'abandoned_guest_cart_retention_days',
        'abandoned_user_cart_retention_months',
    ];

    protected array $casts = [
        'abandoned_guest_cart_retention_days' => IntegerCast::class,
        'abandoned_user_cart_retention_months' => IntegerCast::class,
    ];
}

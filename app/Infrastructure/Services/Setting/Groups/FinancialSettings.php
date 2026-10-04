<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class FinancialSettings extends BaseSettings
{
    public const STORE_WITHDRAWAL_MINIMUM_AMOUNT = 50000;
    public const STORE_WITHDRAWAL_MAXIMUM_AMOUNT = 20000000;

    protected string $group = 'financial';

    protected array $allowedKeys = [
        'withdrawal_enabled',
        'store_withdrawal_minimum_amount',
        'store_withdrawal_maximum_amount',
    ];

    protected array $casts = ['withdrawal_enabled' => BooleanCast::class];
}

<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class FinancialSettings extends BaseSettings
{
    protected string $group = 'financial';

    protected array $allowedKeys = ['withdrawal_enabled'];

    protected array $casts = ['withdrawal_enabled' => BooleanCast::class];
}

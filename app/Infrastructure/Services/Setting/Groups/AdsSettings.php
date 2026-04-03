<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class AdsSettings extends BaseSettings
{
    protected string $group = 'ads';

    protected array $allowedKeys = [
        'project_ads',
    ];

    protected array $casts = [
        'project_ads' => RepeaterArrayCast::class,
    ];
}

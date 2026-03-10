<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class GeneralSettings extends BaseSettings
{
    protected string $group = 'general';
    protected array $allowedKeys = [
        'title',
        'subtitle',
        'favicon',
    ];

    protected array $casts = [
        'favicon' => MediaFileCast::class,
    ];
}

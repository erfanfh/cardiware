<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Services\Setting\BaseSettings;

class GeneralSettings extends BaseSettings
{
    protected string $group = 'general';
    protected array $allowedKeys = [
        'title',
        'subtitle',
        'favicon',
    ];
}

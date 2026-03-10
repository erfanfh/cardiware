<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class HeaderSettings extends BaseSettings
{
    protected string $group = 'header';
    protected array $allowedKeys = [
        'logo',
        'ajax_search',
    ];

    protected array $casts = [
        'logo' => MediaFileCast::class,
        'ajax_search' => BooleanCast::class,
    ];
}

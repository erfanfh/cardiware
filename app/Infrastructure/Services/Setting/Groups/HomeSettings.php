<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class HomeSettings extends BaseSettings
{
    protected string $group = 'home';
    
    protected array $allowedKeys = [
        'hero_slider',
    ];

    protected array $casts = [
        'hero_slider' => RepeaterArrayCast::class,
    ];
}

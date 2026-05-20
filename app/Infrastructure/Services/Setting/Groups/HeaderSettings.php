<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class HeaderSettings extends BaseSettings
{
    protected string $group = 'header';
    protected array $allowedKeys = [
        'active_tab',

        'announcement_active',
        'announcement_text',
        'announcement_button_text',
        'announcement_button_url',
        'announcement_background_image',
        'announcement_background_color',
        'announcement_text_color',
        'logo',
        'notifications',
    ];

    protected array $casts = [
        'announcement_background_image' => MediaFileCast::class,
        'logo' => MediaFileCast::class,
        'notifications' => RepeaterArrayCast::class,
    ];
}

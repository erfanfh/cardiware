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
        'announcement_background_type',
        'announcement_gradient_color',
        'announcement_gradient_angle',
        'announcement_animation_duration',
        'announcement_pattern_size',
        'announcement_pattern_image',
        'announcement_text_color',
        'logo',
        'notifications',
    ];

    protected array $casts = [
        'announcement_background_image' => MediaFileCast::class,
        'announcement_pattern_image' => MediaFileCast::class,
        'logo' => MediaFileCast::class,
        'notifications' => RepeaterArrayCast::class,
    ];
}

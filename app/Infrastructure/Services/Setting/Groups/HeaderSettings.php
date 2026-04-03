<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class HeaderSettings extends BaseSettings
{
    protected string $group = 'header';
    protected array $allowedKeys = [
        'announcement_active',
        'announcement_text',
        'announcement_button_text',
        'announcement_button_url',
        'announcement_background_image',
        'announcement_background_color',
        'announcement_text_color',
        'logo',
        'ajax_search',
        'notifications',
    ];

    protected array $casts = [
        'announcement_background_image' => MediaFileCast::class,
        'logo' => MediaFileCast::class,
        'ajax_search' => BooleanCast::class,
        'notifications' => RepeaterArrayCast::class,
    ];
}

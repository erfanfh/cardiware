<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class LauncherSettings extends BaseSettings
{
    protected string $group = 'launcher';

    protected array $allowedKeys = [
        'badge_text',
        'title',
        'subtitle',
        'description',
        'download_button_text',
        'back_button_text',
        'matrix_enabled',
        'matrix_letters',
        'matrix_speed',
        'features',
        'previews_title',
        'previews',
        'tutorial_title',
        'tutorial_description',
        'tutorial_steps',
        'tutorial_video_url',
        'tutorial_button_text',
        'tutorial_button_url',
    ];

    protected array $casts = [
        'matrix_enabled' => BooleanCast::class,
        'features' => RepeaterArrayCast::class,
        'previews' => RepeaterArrayCast::class,
        'tutorial_steps' => RepeaterArrayCast::class,
    ];
}

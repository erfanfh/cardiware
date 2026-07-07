<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class AuthSettings extends BaseSettings
{
    protected string $group = 'auth';

    protected array $allowedKeys = [
        'label',
        'button_title',
        'button_link',
        'image',
        'announcement_text',
    ];

    protected array $casts = [
        'image' => MediaFileCast::class,
    ];
}

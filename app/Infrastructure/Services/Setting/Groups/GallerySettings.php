<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class GallerySettings extends BaseSettings
{
    protected string $group = 'gallery';

    protected array $allowedKeys = [
        'page_title',
        'page_subtitle',
        'submit_enabled',
        'per_page',
        'image_points',
        'video_points',
    ];

    protected array $casts = [
        'submit_enabled' => BooleanCast::class,
    ];
}

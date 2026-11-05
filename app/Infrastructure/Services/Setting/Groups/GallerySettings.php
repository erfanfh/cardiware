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
        'second_points_percent',
        'later_points_percent',
        'monthly_rewarded_limit',
        'monthly_submission_limit',
    ];

    protected array $casts = [
        'submit_enabled' => BooleanCast::class,
    ];
}

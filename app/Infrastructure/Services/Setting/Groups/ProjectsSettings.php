<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\ArrayCast;
use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class ProjectsSettings extends BaseSettings
{
    protected string $group = 'projects';

    protected array $allowedKeys = [
        'first_box_title',
        'first_box_subtitle',
        'first_box_label',
        'first_box_image',
    ];

    protected array $casts = [
        'first_box_image' => MediaFileCast::class,
    ];
}

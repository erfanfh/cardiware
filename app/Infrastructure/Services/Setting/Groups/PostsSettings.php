<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\ArrayCast;
use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class PostsSettings extends BaseSettings
{
    protected string $group = 'posts';

    protected array $allowedKeys = [
        'first_box_title',
        'first_box_subtitle',
        'first_box_label',
    ];
}

<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\IntegerCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class PostsSettings extends BaseSettings
{
    protected string $group = 'posts';

    protected array $allowedKeys = [
        'first_box_title',
        'first_box_subtitle',
        'first_box_label',
        'posts_per_page',
    ];


    protected array $casts = [
        'posts_per_page' => IntegerCast::class,
    ];
}

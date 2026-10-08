<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class SidebarWidgetsSettings extends BaseSettings
{
    protected string $group = 'sidebar-widgets';
    protected array $allowedKeys = [
        'active_tab',

        'tools_title',
        'tools_subtitle',
        'tools_items',
        'categories_title',
        'categories_subtitle',
        'categories_items',
        'populars_title',
        'populars_subtitle',
        'populars_posts_count',
        'projects_title',
        'projects_subtitle',
        'projects_items',
        'comments_title',
        'comments_subtitle',
        'comments_count',
        'ads_1_display_background',
        'ads_1_body',
        'ads_2_display_background',
        'ads_2_body',
    ];

    protected array $casts = [
        'tools_items' => RepeaterArrayCast::class,
        'categories_items' => RepeaterArrayCast::class,
        'projects_items' => RepeaterArrayCast::class,
        'ads_1_display_background' => BooleanCast::class,
        'ads_2_display_background' => BooleanCast::class,
    ];
}

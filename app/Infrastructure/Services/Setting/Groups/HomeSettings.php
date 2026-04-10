<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\ArrayCast;
use App\Infrastructure\Casts\IntegerCast;
use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class HomeSettings extends BaseSettings
{
    protected string $group = 'home';

    protected array $allowedKeys = [
        'hero_slider',
        'download_products_title',
        'download_products_all_url',
        'download_products_items',
        'special_posts_title',
        'special_posts_posts_counts',
        'subscription_title',
        'subscription_subtitle',
        'subscription_button_title',
        'subscription_button_url',
        'products_title',
        'products_all_products_url',
        'products_items',
        'videos_title',
        'videos_category_ids',
        'videos_description',
        'videos_button_title',
        'videos_button_url',
        'testimonials_title',
        'testimonials_users_avg_scores',
        'testimonials_items',
        'projects_title',
        'projects_subtitle',
        'projects_items',
        'posts_items',
    ];

    protected array $casts = [
        'hero_slider' => RepeaterArrayCast::class,
        'download_products_items' => RepeaterArrayCast::class,
        'products_items' => RepeaterArrayCast::class,
        'testimonials_items' => RepeaterArrayCast::class,
        'projects_items' => RepeaterArrayCast::class,
        'videos_category_ids' => ArrayCast::class,
        'posts_items' => RepeaterArrayCast::class,
        'special_posts_posts_counts' => IntegerCast::class,
    ];
}

<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\ArrayCast;
use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Casts\IntegerCast;
use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class HomeSettings extends BaseSettings
{
    protected string $group = 'home';

    protected array $allowedKeys = [
        'active_tab',
        'hero_slider',
        'download_products_title',
        'download_products_all_url',
        'download_products_slider',
        'download_products_items',
        'special_posts_title',
        'special_posts_items',
        'subscription_title',
        'subscription_subtitle',
        'subscription_description',
        'subscription_image',
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
        'projects_items',
        'posts_items',
        'ads_1_display_background',
        'ads_2_display_background',
        'ads_3_display_background',
        'ads_4_display_background',
        'ads_5_display_background',
        'ads_1_body',
        'ads_2_body',
        'ads_3_body',
        'ads_4_body',
        'ads_5_body',
        'services_options_items',
        'hero_stats',
        'club_cards_show_vip',
        'club_cards_show_projects',
        'club_cards_show_leaders',
        'club_cards_vip_badge',
        'club_cards_projects_subtitle',
        'club_cards_leaders_title',
        'club_cards_leaders_subtitle',
        'club_cards_leaders_limit',
        'club_cards_projects_limit',
    ];

    protected array $casts = [
        'subscription_image' => MediaFileCast::class,
        'hero_slider' => RepeaterArrayCast::class,
        'download_products_items' => RepeaterArrayCast::class,
        'download_products_slider' => BooleanCast::class,
        'special_posts_items' => RepeaterArrayCast::class,
        'products_items' => RepeaterArrayCast::class,
        'testimonials_items' => RepeaterArrayCast::class,
        'projects_items' => RepeaterArrayCast::class,
        'videos_category_ids' => ArrayCast::class,
        'posts_items' => RepeaterArrayCast::class,
        'ads_1_display_background' => BooleanCast::class,
        'ads_2_display_background' => BooleanCast::class,
        'ads_3_display_background' => BooleanCast::class,
        'ads_4_display_background' => BooleanCast::class,
        'ads_5_display_background' => BooleanCast::class,
        'services_options_items' => RepeaterArrayCast::class,
        'hero_stats' => RepeaterArrayCast::class,
        'club_cards_show_vip' => BooleanCast::class,
        'club_cards_show_projects' => BooleanCast::class,
        'club_cards_show_leaders' => BooleanCast::class,
        'club_cards_leaders_limit' => IntegerCast::class,
        'club_cards_projects_limit' => IntegerCast::class,
    ];
}

<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\ArrayCast;
use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class FooterSettings extends BaseSettings
{
    protected string $group = 'footer';

    protected array $allowedKeys = [
        'about',
        'copyright',
        'logo',
        'quality_title',
        'quality_text',
        'commitment_title',
        'commitment_text',
        'store_title',
        'store_url',
        'store_text',
        'column1_title',
        'email',
        'phone',
        'telegram',
        'instagram',
        'eitaa',
        'bale',
        'rubika',
        'soroush',
        'aparat',
        'address_1',
        'address_2',
        'address_3',
        'address_4',
        'news_enabled',
        'news_title',
        'news_items',
        'column2_title',
        'column2_items',
        'column3_title',
        'column3_items',
        'column4_title',
        'column4_image',
        'column4_image_dark',
        'namad_code',
        'stats_bg_image',
        'stats_animation_enabled',
        'background_image',
        'background_pattern',
    ];

    protected array $casts = [
        'logo' => MediaFileCast::class,
        'column2_items' => ArrayCast::class,
        'column3_items' => ArrayCast::class,
        'news_items' => RepeaterArrayCast::class,
        'column4_image' => MediaFileCast::class,
        'column4_image_dark' => MediaFileCast::class,
        'stats_bg_image' => MediaFileCast::class,
        'background_image' => MediaFileCast::class,
        'stats_animation_enabled' => BooleanCast::class,
    ];
}

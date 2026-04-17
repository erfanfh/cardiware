<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Services\Setting\BaseSettings;

class SeoSettings extends BaseSettings
{
    protected string $group = 'seo';
    protected array $allowedKeys = [
        'site_name',
        'robots',
        'home_title',
        'home_description',
        'og_locale',
        'og_image',
        'twitter_card',
        'google_site_verification',
        'bing_site_verification',
    ];

    protected array $casts = [
    ];
}

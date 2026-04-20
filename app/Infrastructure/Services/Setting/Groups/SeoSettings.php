<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class SeoSettings extends BaseSettings
{
    protected string $group = 'seo';
    protected array $allowedKeys = [
        'site_name',
        'alternative_site_name',
        'blog_description',
        'separator',
        'robots',

        'og_locale',
        'twitter_card',
        'google_site_verification',
        'bing_site_verification',

        'home_title',
        'home_description',

        'organization_name',
        'organization_alternate_name',
        'organization_url',
        'organization_logo',
        'organization_email',
        'organization_phone',
        'organization_legal_name',
        'organization_same_as',

        'post_seo_title',
        'post_seo_description',
        'post_social_seo_title',
        'post_social_seo_description',
    ];

    protected array $casts = [
        'organization_logo' => MediaFileCast::class,
    ];
}

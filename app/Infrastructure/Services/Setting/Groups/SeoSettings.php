<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class SeoSettings extends BaseSettings
{
    protected string $group = 'seo';
    protected array $allowedKeys = [
        'site_name',
        'site_alternate_name',
        'site_description',
        'separator',
        'robots',

        'og_locale',
        'twitter_card',
        'google_site_verification',
        'bing_site_verification',

        'home_title',
        'home_description',

        'schema_language',

        'organization_name',
        'organization_alternate_name',
        'organization_founding_date',
        'organization_logo',
        'organization_email',
        'organization_phone',
        'organization_legal_name',
        'organization_same_as',
        'organization_number_of_employees',

        'post_seo_title',
        'post_seo_description',
        'post_social_seo_title',
        'post_social_seo_description',
    ];

    protected array $casts = [
        'organization_logo' => MediaFileCast::class,
    ];
}

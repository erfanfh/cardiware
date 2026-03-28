<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class AboutUsSettings extends BaseSettings
{
    protected string $group = 'about_us';
    protected array $allowedKeys = [
        'hero_badge',
        'hero_title',
        'hero_description',
        'our_mission_box_title',
        'our_mission_description',
        'what_we_build_box_title',
        'what_we_build_items',
        'projects_box_title',
        'our_team_box_title',
        'faqs_box_title',
        'faqs_items',
        'contact_us_box_title',
        'contact_box_title',
        'contact_telegram',
        'contact_instagram',
        'contact_email',
        'contact_phone',
        'contact_hour',
    ];
}

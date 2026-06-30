<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\BooleanCast;
use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class GeneralSettings extends BaseSettings
{
    protected string $group = 'general';
    protected array $allowedKeys = [
        'active_tab',
        'title',
        'subtitle',
        'default_sms_provider',
        'smsir_tepmlate_id',
        'smsir_api_token',
        'farazsms_tepmlate_id',
        'farazsms_api_token',
        'farazsms_from_mobile',
        'melipayamak_username',
        'melipayamak_password',
        'melipayamak_otp_pattern',
        'select_related_content_settings',
        'category_tag_posts_count_settings',
        'search_posts_count_settings',
        'search_projects_count_settings',
        'search_products_count_settings',
        'author_posts_count_settings',

        'default_payment_gateway',
        'zibal_sandbox',
        'zibal_merchant_id',
        'zarinpal_merchant_id',
        'zarinpal_sandbox',
        'payping_sandbox',
        'payping_token',

        'goftino_code',
    ];

    protected array $casts = [
        'favicon' => MediaFileCast::class,
        'zibal_sandbox' => BooleanCast::class,
        'zarinpal_sandbox' => BooleanCast::class,
        'payping_sandbox' => BooleanCast::class,
    ];
}

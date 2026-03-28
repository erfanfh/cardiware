<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class GeneralSettings extends BaseSettings
{
    protected string $group = 'general';
    protected array $allowedKeys = [
        'title',
        'subtitle',
        'favicon',
        'default_sms_provider',
        'smsir_tepmlate_id',
        'smsir_api_token',
        'farazsms_tepmlate_id',
        'farazsms_api_token',
        'farazsms_from_mobile',
        'melli_payamak_username',
        'melli_payamak_password',
        'melli_payamak_from_mobile',
    ];

    protected array $casts = [
        'favicon' => MediaFileCast::class,
    ];
}

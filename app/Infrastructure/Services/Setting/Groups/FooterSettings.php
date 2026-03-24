<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\ArrayCast;
use App\Infrastructure\Casts\MediaFileCast;
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
        'address_1',
        'address_2',
        'address_3',
        'address_4',
        'column2_title',
        'column2_items',
        'column3_title',
        'column3_items',
        'column4_title',
    ];

    protected array $casts = [
        'logo' => MediaFileCast::class,
        'column2_items' => ArrayCast::class,
        'column3_items' => ArrayCast::class,
    ];
}

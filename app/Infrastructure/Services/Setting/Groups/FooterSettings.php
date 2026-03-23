<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\MediaFileCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class FooterSettings extends BaseSettings
{
    protected string $group = 'footer';
    protected array $allowedKeys = [
        'footer_about',
        'footer_copyright',
        'footer_quality_text',
        'footer_commitment_text',
        'footer_store_text',
    ];
}

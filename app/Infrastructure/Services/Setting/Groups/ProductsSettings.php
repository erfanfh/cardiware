<?php

namespace App\Infrastructure\Services\Setting\Groups;

use App\Infrastructure\Casts\IntegerCast;
use App\Infrastructure\Casts\RepeaterArrayCast;
use App\Infrastructure\Services\Setting\BaseSettings;

class ProductsSettings extends BaseSettings
{
    protected string $group = 'products';

    protected array $allowedKeys = [
        'first_box_title',
        'first_box_subtitle',
        'first_box_label',
        'products_per_page',
        'hero_slider',
    ];


    protected array $casts = [
        'products_per_page' => IntegerCast::class,
        'hero_slider' => RepeaterArrayCast::class,
    ];
}

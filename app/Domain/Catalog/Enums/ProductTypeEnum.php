<?php

namespace App\Domain\Catalog\Enums;

enum ProductTypeEnum: string
{
    case DIGITAL = 'digital';
    case PHYSICAL = 'physical';

    public function label(): string
    {
        return match ($this) {
            self::DIGITAL => 'دیجیتال',
            self::PHYSICAL => 'فیزیکی',
        };
    }
}

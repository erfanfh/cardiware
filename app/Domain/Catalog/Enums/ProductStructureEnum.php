<?php

namespace App\Domain\Catalog\Enums;

enum ProductStructureEnum: string
{
    case SIMPLE = 'simple';
    case VARIABLE = 'variable';

    public function label(): string
    {
        return match ($this) {
            self::SIMPLE => 'ساده',
            self::VARIABLE => 'متغیر',
        };
    }
}

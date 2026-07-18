<?php

namespace App\Domain\Catalog\Enums;

enum ProductContentTypeEnum: string
{
    case GAME = 'game';
    case PATCH = 'patch';

    public function label(): string
    {
        return match ($this) {
            self::GAME => 'Game',
            self::PATCH => 'Patch',
        };
    }
}

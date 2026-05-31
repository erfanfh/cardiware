<?php

namespace App\Domain\Catalog\Enums;

enum ProductStatusEnum: string
{
    case ACTIVE = 'active';
    case UNDER_REVIEW = 'under_review';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'فعال',
            self::UNDER_REVIEW => 'در حال بررسی',
            self::INACTIVE => 'غیرفعال',
        };
    }
}

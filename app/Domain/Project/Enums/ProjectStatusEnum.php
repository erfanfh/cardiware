<?php

namespace App\Domain\Project\Enums;

enum ProjectStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'فعال',
            self::INACTIVE => 'غیرفعال'
        };
    }
}

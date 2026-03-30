<?php

namespace App\Domain\Feature\Enums;

enum FeatureKeyEnum: string
{
    case IS_FEATURED = 'is_featured';
    case IS_VIP = 'is_vip';
    case IS_UPDATED = 'is_updated';
    case FRONT_BADGE = 'front_badge';

    /**
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

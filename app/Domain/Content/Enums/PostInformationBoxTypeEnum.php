<?php

namespace App\Domain\Content\Enums;

enum PostInformationBoxTypeEnum: string
{
    case INSTALL_GUIDE = 'install_guide';
    case SYSTEM_REQUIREMENTS = 'system_requirements';
    case PREREQUISITES = 'prerequisites';
    case OTHERS_1 = 'others_1';
    case OTHERS_2 = 'others_2';

    public function label(): string
    {
        return match ($this) {
            self::INSTALL_GUIDE => 'راهنمای نصب',
            self::SYSTEM_REQUIREMENTS => 'سیستم مورد نیاز',
            self::PREREQUISITES => 'پیش نیاز ها',
            self::OTHERS_1 => 'سایر 1',
            self::OTHERS_2 => 'سایر 2',
        };
    }
}

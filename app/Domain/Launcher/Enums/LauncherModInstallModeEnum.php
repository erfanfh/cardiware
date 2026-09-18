<?php

namespace App\Domain\Launcher\Enums;

enum LauncherModInstallModeEnum: string
{
    case TOGGLE = 'toggle';
    case PERMANENT = 'permanent';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::TOGGLE => 'قابل فعال/غیرفعال‌سازی',
            self::PERMANENT => 'نصب دائمی (بدون غیرفعال‌سازی)',
        };
    }
}

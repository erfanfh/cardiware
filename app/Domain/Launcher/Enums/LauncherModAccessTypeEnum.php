<?php

namespace App\Domain\Launcher\Enums;

enum LauncherModAccessTypeEnum: string
{
    case FREE = 'free';
    case PURCHASE = 'purchase';

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
            self::FREE => 'رایگان',
            self::PURCHASE => 'خرید تکی',
        };
    }
}

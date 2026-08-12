<?php

namespace App\Domain\Launcher\Enums;

enum LauncherModInstallTargetEnum: string
{
    case PRODUCT_ROOT = 'product_root';
    case WINDOWS_DOCUMENTS = 'windows_documents';

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
            self::PRODUCT_ROOT => 'ریشه محصول',
            self::WINDOWS_DOCUMENTS => 'Documents ویندوز',
        };
    }
}

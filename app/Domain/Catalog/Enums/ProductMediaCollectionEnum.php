<?php

namespace App\Domain\Catalog\Enums;

enum ProductMediaCollectionEnum: string
{
    case IMAGE_GALLERY = 'image_gallery';
    case VIDEO_GALLERY = 'video_gallery';

    public function label(): string
    {
        return match ($this) {
            self::IMAGE_GALLERY => 'گالری تصاویر',
            self::VIDEO_GALLERY => 'گالری ویدیو',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
    }
}

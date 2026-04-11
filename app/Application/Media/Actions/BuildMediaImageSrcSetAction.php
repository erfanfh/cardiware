<?php

namespace App\Application\Media\Actions;

use App\Domain\Media\Enums\MediaFileVariantEnum;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Support\Facades\Storage;

class BuildMediaImageSrcSetAction
{
    /**
     * Build an HTML srcset string from available variants.
     */
    public function execute(MediaFile $mediaFile): string
    {
        if (!$mediaFile->relationLoaded('variants')) {
            $mediaFile->load('variants');
        }

        $orderedVariants = [
            MediaFileVariantEnum::THUMBNAIL,
            MediaFileVariantEnum::MEDIUM,
            MediaFileVariantEnum::MEDIUM_LARGE,
            MediaFileVariantEnum::LARGE,
            MediaFileVariantEnum::ORIGINAL,
        ];

        $parts = [];

        foreach ($orderedVariants as $variantEnum) {
            $variant = $mediaFile->variants->firstWhere('variant', $variantEnum);

            if (!$variant || empty($variant->width)) {
                continue;
            }

            $url = Storage::disk($variant->disk->value)->url($variant->path);

            $parts[] = "{$url} {$variant->width}w";
        }

        return implode(', ', $parts);
    }
}

<?php

namespace App\Application\Media\Services;

use App\Domain\Media\Enums\MediaFileVariantEnum;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Media\Models\MediaFileVariant;
use Illuminate\Support\Facades\Storage;

class MediaImagePresenter
{
    public function __construct(
        private readonly MediaFile $mediaFile,
    )
    {
    }

    public function url(MediaFileVariantEnum $variant = MediaFileVariantEnum::ORIGINAL): ?string
    {
        $target = $this->findVariant($variant) ?? $this->mediaFile->primaryVariant;

        if (!$target) {
            return null;
        }

        return Storage::disk($target->disk->value)->url($target->path);
    }

    public function srcSet(): string
    {
        $orderedVariants = [
            MediaFileVariantEnum::THUMBNAIL,
            MediaFileVariantEnum::MEDIUM,
            MediaFileVariantEnum::MEDIUM_LARGE,
            MediaFileVariantEnum::LARGE,
            MediaFileVariantEnum::ORIGINAL,
        ];

        $parts = [];

        foreach ($orderedVariants as $variantEnum) {
            $variant = $this->findVariant($variantEnum);

            if (!$variant || !$variant->width) {
                continue;
            }

            $parts[] = sprintf(
                '%s %sw',
                Storage::disk($variant->disk->value)->url($variant->path),
                $variant->width
            );
        }

        return implode(', ', $parts);
    }

    private function findVariant(MediaFileVariantEnum $variant): ?MediaFileVariant
    {
        if (!$this->mediaFile->relationLoaded('variants')) {
            $this->mediaFile->load('variants');
        }

        return $this->mediaFile->variants->firstWhere('variant', $variant);
    }
}

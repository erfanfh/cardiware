<?php

namespace App\Application\Media\Actions;

use App\Domain\Media\Enums\MediaFileVariantEnum;
use App\Domain\Media\Models\MediaFile;
use Illuminate\Support\Facades\Storage;

class GetMediaFileUrlAction
{
    /**
     * Resolve the URL of a specific media file variant.
     */
    public function execute(
        MediaFile            $mediaFile,
        MediaFileVariantEnum $variant = MediaFileVariantEnum::ORIGINAL,
    ): ?string
    {
        $mediaFile->loadMissing(['primaryVariant', 'variants']);

        $targetVariant = $mediaFile->variants->firstWhere('variant', $variant);

        if (!$targetVariant) {
            $targetVariant = $mediaFile->primaryVariant;
        }

        if (!$targetVariant) {
            return null;
        }

        return Storage::disk($targetVariant->disk->value)->url($targetVariant->path);
    }
}

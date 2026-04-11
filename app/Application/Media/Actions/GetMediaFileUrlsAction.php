<?php

namespace App\Application\Media\Actions;

use App\Domain\Media\Models\MediaFile;
use App\Domain\Media\Models\MediaFileVariant;
use Illuminate\Support\Facades\Storage;

class GetMediaFileUrlsAction
{
    /**
     * Resolve all available URLs for a media file without extra queries
     * when variants are already eager loaded.
     *
     * @param MediaFile $mediaFile
     * @return array<string, string>
     */
    public function execute(MediaFile $mediaFile): array
    {
        if (!$mediaFile->relationLoaded('variants')) {
            $mediaFile->load('variants');
        }

        return $mediaFile->variants
            ->mapWithKeys(function (MediaFileVariant $variant) {
                return [
                    $variant->variant->value => Storage::disk($variant->disk->value)->url($variant->path),
                ];
            })
            ->toArray();
    }
}

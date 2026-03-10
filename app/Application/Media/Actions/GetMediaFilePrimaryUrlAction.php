<?php

namespace App\Application\Media\Actions;

use App\Domain\Media\Models\MediaFile;
use Illuminate\Support\Facades\Storage;

class GetMediaFilePrimaryUrlAction
{
    /**
     * Resolves the public URL of a media file's primary variant.
     *
     * @param MediaFile $mediaFile
     * @return string|null
     */
    public function execute(MediaFile $mediaFile): ?string
    {
        if (empty($mediaFile->primary_variant_id))
            return null;

        $variant = $mediaFile->primaryVariant;

        if (!$variant)
            return null;

        // برای disk های public، url می‌ده (local public یا s3 public)
        return Storage::disk($variant->disk->value)->url($variant->path);
    }
}


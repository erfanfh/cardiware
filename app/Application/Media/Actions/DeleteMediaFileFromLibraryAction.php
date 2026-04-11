<?php

namespace App\Application\Media\Actions;

use App\Domain\Media\Models\MediaFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DeleteMediaFileFromLibraryAction
{
    /**
     * @throws Throwable
     */
    public function execute(MediaFile $mediaFile): void
    {
        $mediaFile->loadMissing(['variants']);

        DB::transaction(function () use ($mediaFile) {
            foreach ($mediaFile->variants as $variant) {
                try {
                    Storage::disk($variant->disk->value)->delete($variant->path);
                } catch (Throwable $e) {
                    Log::warning('Failed to delete media variant file.', [
                        'media_file_id' => $mediaFile->id,
                        'variant_id' => $variant->id,
                        'disk' => $variant->disk->value,
                        'path' => $variant->path,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $mediaFile->variants()->delete();
            $mediaFile->delete();
        });
    }
}

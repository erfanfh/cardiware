<?php

namespace App\Application\Media\Actions;

use App\Domain\Media\Models\MediaFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Log;
use Throwable;

class DeleteMediaFileFromLibraryAction
{
    /**
     * Execute the media file deletion process.
     *
     * @param MediaFile $mediaFile
     * @return void
     * @throws Throwable
     */
    public function execute(MediaFile $mediaFile): void
    {
        $mediaFile->loadMissing(['variants']);

        DB::transaction(function () use ($mediaFile) {
            foreach ($mediaFile->variants as $variant) {
                $disk = $variant->disk?->value ?? $variant->disk;
                $path = $variant->path;

                if ($disk && $path) {
                    try {
                        Storage::disk($disk)->delete($path);
                    } catch (Throwable $e) {
                        Log::info($e);
                    }
                }
            }

            $mediaFile->variants()->delete();

            $mediaFile->delete();
        });
    }
}

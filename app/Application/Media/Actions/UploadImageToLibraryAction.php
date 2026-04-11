<?php

namespace App\Application\Media\Actions;

use App\Application\Media\Services\ImageSniffer;
use App\Application\Media\Services\ImageVariantGenerator;
use App\Domain\Media\Enums\MediaFileDiskEnum;
use App\Domain\Media\Enums\MediaFileFormatEnum;
use App\Domain\Media\Enums\MediaFileStatusEnum;
use App\Domain\Media\Enums\MediaFileTypeEnum;
use App\Domain\Media\Enums\MediaFileVariantEnum;
use App\Domain\Media\Enums\MediaFileVisibilityEnum;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Media\Models\MediaFileVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;
use Throwable;

/**
 * Upload an image to the media library and create all configured WebP variants.
 */
readonly class UploadImageToLibraryAction
{
    public function __construct(
        private ImageSniffer          $sniffer,
        private ImageVariantGenerator $variantGenerator,
    )
    {
    }

    /**
     * @throws RuntimeException|Throwable
     */
    public function execute(
        TemporaryUploadedFile   $file,
        ?int                    $createdBy,
        MediaFileVisibilityEnum $visibility = MediaFileVisibilityEnum::PUBLIC,
        MediaFileDiskEnum       $disk = MediaFileDiskEnum::PUBLIC,
    ): MediaFile
    {
        $ext = strtolower($file->getClientOriginalExtension() ?: '');

        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new RuntimeException('Invalid image extension.');
        }

        $binary = file_get_contents($file->getRealPath());

        if ($binary === false || $binary === '') {
            throw new RuntimeException('Unable to read uploaded file.');
        }

        $meta = $this->sniffer->sniff($binary);

        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/webp',
        ];

        if (!in_array($meta['mime'], $allowedMimes, true)) {
            throw new RuntimeException('Invalid MIME type.');
        }

        $maxWidth = (int)config('media.images.max_width', 8000);
        $maxHeight = (int)config('media.images.max_height', 8000);
        $maxPixels = (int)config('media.images.max_pixels', 40_000_000);

        if ($meta['width'] <= 0 || $meta['height'] <= 0) {
            throw new RuntimeException('Invalid image dimensions.');
        }

        if ($meta['width'] > $maxWidth || $meta['height'] > $maxHeight) {
            throw new RuntimeException('Image dimensions are too large.');
        }

        if (($meta['width'] * $meta['height']) > $maxPixels) {
            throw new RuntimeException('Image is too large (pixel count).');
        }

        $expectedExtByMime = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/webp' => ['webp'],
        ];

        if (
            $ext !== ''
            && isset($expectedExtByMime[$meta['mime']])
            && !in_array($ext, $expectedExtByMime[$meta['mime']], true)
        ) {
            throw new RuntimeException('File extension does not match detected MIME type.');
        }

        $generatedVariants = $this->variantGenerator->generate($binary);

        $baseDir = 'media/images/' . now()->format('Y/m');
        $uuid = (string)Str::uuid();
        $storage = Storage::disk($disk->value);
        $storageVisibility = $visibility->value === 'public' ? 'public' : 'private';

        $storedVariants = [];

        foreach ($generatedVariants as $variantKey => $variantData) {
            $suffix = $variantKey === MediaFileVariantEnum::ORIGINAL->value
                ? ''
                : "__{$variantKey}";

            $path = "{$baseDir}/{$uuid}{$suffix}.webp";

            $storage->put($path, $variantData['binary'], [
                'visibility' => $storageVisibility,
            ]);

            $storedVariants[$variantKey] = [
                'path' => $path,
                'binary' => $variantData['binary'],
                'width' => (int)$variantData['width'],
                'height' => (int)$variantData['height'],
                'checksum' => hash('sha256', $variantData['binary']),
                'byte_size' => strlen($variantData['binary']),
            ];
        }

        $title = $this->titleFromOriginalName((string)$file->getClientOriginalName());

        return DB::transaction(function () use (
            $createdBy,
            $visibility,
            $disk,
            $storedVariants,
            $title
        ) {
            $media = MediaFile::query()->create([
                'type' => MediaFileTypeEnum::IMAGE,
                'visibility' => $visibility,
                'created_by' => $createdBy,
                'status' => MediaFileStatusEnum::PROCESSING,
                'title' => $title,
            ]);

            $createdVariantModels = [];

            foreach ($storedVariants as $variantKey => $variant) {
                $variantEnum = MediaFileVariantEnum::from($variantKey);

                $createdVariantModels[$variantKey] = MediaFileVariant::query()->create([
                    'media_file_id' => $media->id,
                    'disk' => $disk,
                    'mime_type' => MediaFileFormatEnum::WEBP->mimeType(),
                    'variant' => $variantEnum,
                    'format' => MediaFileFormatEnum::WEBP,
                    'path' => $variant['path'],
                    'byte_size' => $variant['byte_size'],
                    'width' => $variant['width'],
                    'height' => $variant['height'],
                    'checksum' => $variant['checksum'],
                    'generated_at' => now(),
                ]);
            }

            $media->primary_variant_id = $createdVariantModels[MediaFileVariantEnum::ORIGINAL->value]->id;
            $media->status = MediaFileStatusEnum::READY;
            $media->processed_at = now();
            $media->save();

            return $media->fresh(['primaryVariant', 'variants']);
        });
    }

    private function titleFromOriginalName(string $originalName): ?string
    {
        $name = trim($originalName);

        if ($name === '') {
            return null;
        }

        $name = basename($name);
        $name = pathinfo($name, PATHINFO_FILENAME);
        $name = str_replace(['_', '-'], ' ', $name);
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? $name;
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;
        $name = trim($name);

        if ($name === '') {
            return null;
        }

        return mb_substr($name, 0, 190);
    }
}

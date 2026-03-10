<?php

namespace App\Application\Media\Actions;

use App\Application\Media\Services\ImageSniffer;
use App\Application\Media\Services\WebpImageProcessor;
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
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use RuntimeException;
use Str;
use Throwable;

/**
 * Uploads an image to the media library with strict validation and sanitization.
 */
readonly class UploadImageToLibraryAction
{
    /**
     * @param WebpImageProcessor $processor
     * @param ImageSniffer $sniffer
     */
    public function __construct(
        private WebpImageProcessor $processor,
        private ImageSniffer       $sniffer,
    )
    {
    }

    /**
     * Upload an image into the library and create media records + variants.
     *
     * @param TemporaryUploadedFile $file The temporary uploaded file (Livewire).
     * @param int|null $createdBy The user ID who initiated the upload (nullable for system actions).
     * @param MediaFileVisibilityEnum $visibility Storage visibility for persisted files (public/private).
     * @param MediaFileDiskEnum $disk Storage disk to persist binaries on.
     *
     * @return MediaFile The created media file model, freshly loaded with its primary variant relation.
     *
     * @throws RuntimeException|Throwable If the file extension is invalid, file cannot be read,
     *                          MIME/structure checks fail, or dimensions exceed limits.
     */
    public function execute(
        TemporaryUploadedFile   $file,
        ?int                    $createdBy,
        MediaFileVisibilityEnum $visibility = MediaFileVisibilityEnum::PUBLIC,
        MediaFileDiskEnum       $disk = MediaFileDiskEnum::PUBLIC
    ): MediaFile
    {
        // 1) Basic validation (client/laravel level)
        // هسته‌ی امنیتی واقعی‌تر (magic bytes سخت‌گیرانه‌تر) رو گام بعد هم می‌تونیم قوی‌تر کنیم
        $ext = strtolower($file->getClientOriginalExtension() ?: '');
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            throw new RuntimeException('Invalid image extension.');
        }

        // 2) Read binary
        $binary = file_get_contents($file->getRealPath());
        // --- Hard validation (magic bytes + image structure)
        $meta = $this->sniffer->sniff($binary);

// فقط این MIMEها مجازند
        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/webp',
        ];

        if (!in_array($meta['mime'], $allowedMimes, true)) {
            throw new RuntimeException('Invalid MIME type.');
        }

// محدودیت ابعاد برای جلوگیری از image bomb
        $maxWidth = 8000;
        $maxHeight = 8000;
        $maxPixels = 40_000_000; // 40MP

        if ($meta['width'] <= 0 || $meta['height'] <= 0) {
            throw new RuntimeException('Invalid image dimensions.');
        }

        if ($meta['width'] > $maxWidth || $meta['height'] > $maxHeight) {
            throw new RuntimeException('Image dimensions are too large.');
        }

        if (($meta['width'] * $meta['height']) > $maxPixels) {
            throw new RuntimeException('Image is too large (pixel count).');
        }

// Optional: چک همخوانی extension با mime (برای UX بهتر)
        $ext = strtolower($file->getClientOriginalExtension() ?: '');
        $expectedExtByMime = [
            'image/jpeg' => ['jpg', 'jpeg'],
            'image/png' => ['png'],
            'image/webp' => ['webp'],
        ];

        if ($ext !== '' && isset($expectedExtByMime[$meta['mime']]) && !in_array($ext, $expectedExtByMime[$meta['mime']], true)) {
            // این رو می‌تونی سخت‌گیرانه رد کنی یا فقط log بگیری
            throw new RuntimeException('File extension does not match detected MIME type.');
        }
        if ($binary === false || $binary === '') {
            throw new RuntimeException('Unable to read uploaded file.');
        }

        // 3) Re-encode to WebP (sanitize)
        // variants
        $original = $this->processor->toWebp($binary, maxWidth: 1600, quality: 80);
        $thumb = $this->processor->toWebp($binary, maxWidth: 300, quality: 78);

        // 4) Prepare paths
        $baseDir = 'media/images/' . now()->format('Y/m');
        $uuid = (string)Str::uuid();

        $originalPath = "{$baseDir}/{$uuid}.webp";
        $thumbPath = "{$baseDir}/{$uuid}__thumb.webp";

        // 5) Store binaries
        $storage = Storage::disk($disk->value);

        $storage->put($originalPath, $original['binary'], [
            'visibility' => $visibility->value === 'public' ? 'public' : 'private',
        ]);

        $storage->put($thumbPath, $thumb['binary'], [
            'visibility' => $visibility->value === 'public' ? 'public' : 'private',
        ]);

        $originalChecksum = hash('sha256', $original['binary']);
        $thumbChecksum = hash('sha256', $thumb['binary']);

        $originalName = ($file->getClientOriginalName() ?: '');
        $title = $this->titleFromOriginalName($originalName);

        // 6) DB transaction: create media_file + variants + primary_variant_id
        return DB::transaction(function () use (
            $createdBy,
            $visibility,
            $disk,
            $originalPath,
            $thumbPath,
            $original,
            $thumb,
            $originalChecksum,
            $thumbChecksum,
            $title
        ) {
            $media = MediaFile::query()->create([
                'type' => MediaFileTypeEnum::IMAGE,
                'visibility' => $visibility,
                'created_by' => $createdBy,
                'status' => MediaFileStatusEnum::PROCESSING,
                'title' => $title,
            ]);

            $originalVar = MediaFileVariant::query()->create([
                'media_file_id' => $media->id,
                'disk' => $disk,
                'mime_type' => 'image/webp',
                'variant' => MediaFileVariantEnum::ORIGINAL,
                'format' => MediaFileFormatEnum::WEBP,
                'path' => $originalPath,
                'byte_size' => strlen($original['binary']),
                'width' => (int)($original['width'] ?? 0),
                'height' => (int)($original['height'] ?? 0),
                'checksum' => $originalChecksum,
                'generated_at' => now(),
            ]);

            MediaFileVariant::query()->create([
                'media_file_id' => $media->id,
                'disk' => $disk,
                'mime_type' => 'image/webp',
                'variant' => MediaFileVariantEnum::THUMBNAIL,
                'format' => MediaFileFormatEnum::WEBP,
                'path' => $thumbPath,
                'byte_size' => strlen($thumb['binary']),
                'width' => (int)($thumb['width'] ?? 0),
                'height' => (int)($thumb['height'] ?? 0),
                'checksum' => $thumbChecksum,
                'generated_at' => now(),
            ]);

            $media->primary_variant_id = $originalVar->id;
            $media->status = MediaFileStatusEnum::READY;
            $media->processed_at = now();
            $media->save();

            return $media->fresh(['primaryVariant']);
        });
    }

    /**
     * @param string $originalName
     * @return string|null
     */
    private function titleFromOriginalName(string $originalName): ?string
    {
        $name = trim($originalName);

        if ($name === '')
            return null;

        $name = basename($name);
        $name = pathinfo($name, PATHINFO_FILENAME);
        $name = str_replace(['_', '-'], ' ', $name);
        $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? $name;
        $name = preg_replace('/\s+/u', ' ', $name) ?? $name;
        $name = trim($name);

        if ($name === '')
            return null;

        return mb_substr($name, 0, 190);
    }
}

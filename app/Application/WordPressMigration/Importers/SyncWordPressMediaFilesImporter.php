<?php

namespace App\Application\WordPressMigration\Importers;

use App\Domain\Media\Enums\MediaFileDiskEnum;
use App\Domain\Media\Enums\MediaFileFormatEnum;
use App\Domain\Media\Enums\MediaFileVariantEnum;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Media\Models\MediaFileVariant;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class SyncWordPressMediaFilesImporter
{
    private string $baseUrl = 'https://Cardiware.ir/wp-content/uploads/';

    private int $processed = 0;
    private int $missingMap = 0;
    private int $missingAttachedFile = 0;
    private int $originalDownloaded = 0;
    private int $variantDownloaded = 0;
    private int $originalVariantsCreated = 0;
    private int $imageVariantsCreated = 0;
    private int $variantsUpdated = 0;
    private int $unsupportedFormat = 0;
    private int $downloadFailed = 0;
    private int $skipped = 0;
    private int $failed = 0;

    public function import(?callable $onProgress = null): void
    {
        DB::connection('wordpress')
            ->table('posts')
            ->where('post_type', 'attachment')
            ->orderBy('ID')
            ->chunkById(200, function ($attachments) use ($onProgress) {
                foreach ($attachments as $wpAttachment) {
                    $this->processed++;

                    try {
                        $this->syncAttachment($wpAttachment);
                    } catch (Throwable $exception) {
                        $this->failed++;

                        logger()->error('WordPress media sync failed', [
                            'wordpress_attachment_id' => $wpAttachment->ID,
                            'post_title' => $wpAttachment->post_title ?? null,
                            'post_mime_type' => $wpAttachment->post_mime_type ?? null,
                            'guid' => $wpAttachment->guid ?? null,
                            'message' => $exception->getMessage(),
                            'file' => $exception->getFile(),
                            'line' => $exception->getLine(),
                            'trace' => $exception->getTraceAsString(),
                        ]);
                    }

                    if ($onProgress) {
                        $onProgress($this->stats());
                    }
                }
            }, 'ID');
    }

    public function total(): int
    {
        return DB::connection('wordpress')
            ->table('posts')
            ->where('post_type', 'attachment')
            ->count();
    }

    public function stats(): array
    {
        return [
            'processed' => $this->processed,
            'missing_map' => $this->missingMap,
            'missing_attached_file' => $this->missingAttachedFile,
            'original_downloaded' => $this->originalDownloaded,
            'variant_downloaded' => $this->variantDownloaded,
            'original_variants_created' => $this->originalVariantsCreated,
            'image_variants_created' => $this->imageVariantsCreated,
            'variants_updated' => $this->variantsUpdated,
            'unsupported_format' => $this->unsupportedFormat,
            'download_failed' => $this->downloadFailed,
            'skipped' => $this->skipped,
            'failed' => $this->failed,
        ];
    }

    private function syncAttachment(object $wpAttachment): void
    {
        $map = WpMigrationMap::query()
            ->where('source_type', 'wordpress_attachment')
            ->where('source_id', $wpAttachment->ID)
            ->where('target_type', MediaFile::class)
            ->first();

        if (!$map) {
            $this->missingMap++;

            logger()->warning('WordPress media sync skipped because attachment map was not found', [
                'wordpress_attachment_id' => $wpAttachment->ID,
                'post_title' => $wpAttachment->post_title ?? null,
                'guid' => $wpAttachment->guid ?? null,
            ]);

            return;
        }

        $mediaFile = MediaFile::query()->find($map->target_id);

        if (!$mediaFile) {
            $this->missingMap++;

            logger()->warning('WordPress media sync skipped because target media file was not found', [
                'wordpress_attachment_id' => $wpAttachment->ID,
                'target_media_file_id' => $map->target_id,
            ]);

            return;
        }

        $attachedFile = $this->getMeta((int)$wpAttachment->ID, '_wp_attached_file');

        if (!$attachedFile) {
            $this->missingAttachedFile++;

            logger()->warning('WordPress media sync skipped because attached file meta is missing', [
                'wordpress_attachment_id' => $wpAttachment->ID,
                'post_title' => $wpAttachment->post_title ?? null,
                'guid' => $wpAttachment->guid ?? null,
            ]);

            return;
        }

        $metadata = $this->parseAttachmentMetadata(
            $this->getMeta((int)$wpAttachment->ID, '_wp_attachment_metadata')
        );

        $originalPath = $this->normalizePath($attachedFile);

        $downloadedOriginal = $this->downloadIfMissing($originalPath);

        if ($downloadedOriginal) {
            $this->originalDownloaded++;
        }

        $originalVariant = $this->syncVariant(
            mediaFile: $mediaFile,
            variant: MediaFileVariantEnum::ORIGINAL,
            path: $originalPath,
            mimeType: $wpAttachment->post_mime_type,
            width: $metadata['width'] ?? null,
            height: $metadata['height'] ?? null,
            isOriginal: true
        );

        if ($originalVariant && !$mediaFile->primary_variant_id) {
            $mediaFile->update([
                'primary_variant_id' => $originalVariant->id,
            ]);
        }

        $this->syncWordPressImageSizes($mediaFile, $originalPath, $metadata);
    }

    private function syncWordPressImageSizes(MediaFile $mediaFile, string $originalPath, array $metadata): void
    {
        $sizes = $metadata['sizes'] ?? [];

        if (!is_array($sizes)) {
            return;
        }

        $map = [
            'thumbnail' => MediaFileVariantEnum::THUMBNAIL,
            'medium' => MediaFileVariantEnum::MEDIUM,
            'medium_large' => MediaFileVariantEnum::MEDIUM_LARGE,
            'large' => MediaFileVariantEnum::LARGE,
        ];

        $directory = trim(dirname($originalPath), '.');

        foreach ($map as $wpSizeKey => $variantEnum) {
            if (!isset($sizes[$wpSizeKey]) || !is_array($sizes[$wpSizeKey])) {
                $this->skipped++;
                continue;
            }

            $size = $sizes[$wpSizeKey];

            if (empty($size['file'])) {
                $this->skipped++;
                continue;
            }

            $variantPath = trim($directory . '/' . $size['file'], '/');

            $downloadedVariant = $this->downloadIfMissing($variantPath);

            if ($downloadedVariant) {
                $this->variantDownloaded++;
            }

            $this->syncVariant(
                mediaFile: $mediaFile,
                variant: $variantEnum,
                path: $variantPath,
                mimeType: $size['mime-type'] ?? null,
                width: isset($size['width']) ? (int)$size['width'] : null,
                height: isset($size['height']) ? (int)$size['height'] : null,
                isOriginal: false
            );
        }
    }

    private function syncVariant(
        MediaFile            $mediaFile,
        MediaFileVariantEnum $variant,
        string               $path,
        ?string              $mimeType,
        ?int                 $width,
        ?int                 $height,
        bool                 $isOriginal
    ): ?MediaFileVariant
    {
        $path = $this->normalizePath($path);
        $format = $this->detectFormat($path);

        if (!$format) {
            $this->unsupportedFormat++;

            logger()->warning('WordPress media variant skipped because file format is unsupported', [
                'media_file_id' => $mediaFile->id,
                'variant' => $variant->value,
                'path' => $path,
            ]);

            return null;
        }

        $existingByPath = MediaFileVariant::query()
            ->where('disk', MediaFileDiskEnum::WORDPRESS_UPLOADS)
            ->where('path', $path)
            ->first();

        if ($existingByPath && (int)$existingByPath->media_file_id !== (int)$mediaFile->id) {
            $this->skipped++;

            logger()->warning('WordPress media variant skipped because path belongs to another media file', [
                'media_file_id' => $mediaFile->id,
                'existing_media_file_id' => $existingByPath->media_file_id,
                'variant' => $variant->value,
                'path' => $path,
            ]);

            return null;
        }

        $disk = Storage::disk(MediaFileDiskEnum::WORDPRESS_UPLOADS->value);
        $localPath = $disk->path($path);
        $fileExists = $disk->exists($path);

        $payload = [
            'disk' => MediaFileDiskEnum::WORDPRESS_UPLOADS,
            'mime_type' => $mimeType ?: $format->mimeType(),
            'format' => $format,
            'path' => $path,
            'byte_size' => $fileExists && is_file($localPath) ? filesize($localPath) : 0,
            'width' => $width,
            'height' => $height,
            'checksum' => $fileExists && is_file($localPath) ? md5_file($localPath) : null,
            'generated_at' => now(),
        ];

        $existing = MediaFileVariant::query()
            ->where('media_file_id', $mediaFile->id)
            ->where('variant', $variant)
            ->first();

        if ($existing) {
            $existing->update($payload);
            $this->variantsUpdated++;

            return $existing;
        }

        $created = MediaFileVariant::query()->create([
            'media_file_id' => $mediaFile->id,
            'variant' => $variant,
            ...$payload,
        ]);

        if ($isOriginal) {
            $this->originalVariantsCreated++;
        } else {
            $this->imageVariantsCreated++;
        }

        return $created;
    }

    private function downloadIfMissing(string $path): bool
    {
        $path = $this->normalizePath($path);

        if ($path === '') {
            $this->skipped++;
            return false;
        }

        $disk = Storage::disk(MediaFileDiskEnum::WORDPRESS_UPLOADS->value);

        if ($disk->exists($path)) {
            return false;
        }

        $url = $this->buildRemoteUrl($path);

        $response = Http::timeout(60)
            ->retry(2, 500)
            ->get($url);

        if (!$response->successful()) {
            $this->downloadFailed++;

            logger()->warning('WordPress media file download failed', [
                'path' => $path,
                'url' => $url,
                'status' => $response->status(),
            ]);

            return false;
        }

        $disk->put($path, $response->body());

        return true;
    }

    private function buildRemoteUrl(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        $segments = explode('/', ltrim($path, '/'));

        $encodedPath = collect($segments)
            ->map(fn(string $segment) => rawurlencode($segment))
            ->implode('/');

        return rtrim($this->baseUrl, '/') . '/' . $encodedPath;
    }

    private function getMeta(int $postId, string $key): ?string
    {
        $value = DB::connection('wordpress')
            ->table('postmeta')
            ->where('post_id', $postId)
            ->where('meta_key', $key)
            ->value('meta_value');

        return $value !== null && $value !== '' ? (string)$value : null;
    }

    private function parseAttachmentMetadata(?string $value): array
    {
        if (!$value) {
            return [];
        }

        $data = @unserialize($value, ['allowed_classes' => false]);

        return is_array($data) ? $data : [];
    }

    private function detectFormat(string $path): ?MediaFileFormatEnum
    {
        $extension = strtolower(pathinfo(parse_url($path, PHP_URL_PATH) ?: $path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg' => MediaFileFormatEnum::JPG,
            'jpeg' => MediaFileFormatEnum::JPEG,
            'png' => MediaFileFormatEnum::PNG,
            'webp' => MediaFileFormatEnum::WEBP,
            'gif' => MediaFileFormatEnum::GIF,
            'svg' => MediaFileFormatEnum::SVG,
            'ico' => MediaFileFormatEnum::ICO,
            'pdf' => MediaFileFormatEnum::PDF,
            'zip' => MediaFileFormatEnum::ZIP,
            'rar' => MediaFileFormatEnum::RAR,
            'json' => MediaFileFormatEnum::JSON,
            'docx' => MediaFileFormatEnum::DOCX,
            'mp3' => MediaFileFormatEnum::MP3,
            'wav' => MediaFileFormatEnum::WAV,
            'mp4' => MediaFileFormatEnum::MP4,
            'woff' => MediaFileFormatEnum::WOFF,
            'woff2' => MediaFileFormatEnum::WOFF2,
            'ttf' => MediaFileFormatEnum::TTF,
            default => null,
        };
    }

    private function normalizePath(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) {
            $parsedPath = parse_url($path, PHP_URL_PATH);

            $path = ltrim(urldecode((string)$parsedPath), '/');

            return Str::after($path, 'wp-content/uploads/');
        }

        return ltrim($path, '/');
    }
}

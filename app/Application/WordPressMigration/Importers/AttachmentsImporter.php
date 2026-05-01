<?php

namespace App\Application\WordPressMigration\Importers;

use App\Domain\Media\Enums\MediaFileDiskEnum;
use App\Domain\Media\Enums\MediaFileFormatEnum;
use App\Domain\Media\Enums\MediaFileStatusEnum;
use App\Domain\Media\Enums\MediaFileTypeEnum;
use App\Domain\Media\Enums\MediaFileVariantEnum;
use App\Domain\Media\Enums\MediaFileVisibilityEnum;
use App\Domain\Media\Models\MediaFile;
use App\Domain\Media\Models\MediaFileVariant;
use App\Domain\WordPressMigration\Models\WpMigrationMap;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class AttachmentsImporter
{
    private int $processed = 0;
    private int $created = 0;
    private int $reused = 0;
    private int $skipped = 0;
    private int $unsupported = 0;
    private int $failed = 0;

    public function import(?callable $onProgress = null): void
    {
        DB::connection('wordpress')
            ->table('posts')
            ->where('post_type', 'attachment')
            ->orderBy('ID')
            ->chunkById(300, function ($attachments) use ($onProgress) {
                foreach ($attachments as $wpAttachment) {
                    $this->processed++;

                    try {
                        $this->importAttachment($wpAttachment);
                    } catch (Throwable $exception) {
                        $this->failed++;

                        logger()->error('WordPress attachment import failed', [
                            'wordpress_attachment_id' => $wpAttachment->ID,
                            'message' => $exception->getMessage(),
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
            'created' => $this->created,
            'reused' => $this->reused,
            'skipped' => $this->skipped,
            'unsupported' => $this->unsupported,
            'failed' => $this->failed,
        ];
    }

    private function importAttachment(object $wpAttachment): void
    {
        $exists = WpMigrationMap::query()
            ->where('source_type', 'wordpress_attachment')
            ->where('source_id', $wpAttachment->ID)
            ->where('target_type', MediaFile::class)
            ->exists();

        if ($exists) {
            $this->skipped++;
            return;
        }

        $attachedFile = $this->getMeta((int)$wpAttachment->ID, '_wp_attached_file');

        if (!$attachedFile) {
            $this->skipped++;
            return;
        }

        $normalizedPath = $this->normalizePath($attachedFile);
        $format = $this->detectFormat($normalizedPath);

        if (!$format) {
            $this->unsupported++;
            logger()->warning('Unsupported WordPress attachment format', [
                'wordpress_attachment_id' => $wpAttachment->ID,
                'path' => $attachedFile,
                'mime_type' => $wpAttachment->post_mime_type,
            ]);

            return;
        }

        $metadata = $this->parseAttachmentMetadata(
            $this->getMeta((int)$wpAttachment->ID, '_wp_attachment_metadata')
        );

        $existingVariant = MediaFileVariant::query()
            ->where('disk', MediaFileDiskEnum::WORDPRESS_UPLOADS)
            ->where('path', $normalizedPath)
            ->first();

        if ($existingVariant) {
            WpMigrationMap::query()->create([
                'source_type' => 'wordpress_attachment',
                'source_id' => $wpAttachment->ID,
                'target_type' => MediaFile::class,
                'target_id' => $existingVariant->media_file_id,
                'meta' => [
                    'attached_file' => $attachedFile,
                    'alt' => $this->getMeta((int)$wpAttachment->ID, '_wp_attachment_image_alt'),
                    'mime_type' => $wpAttachment->post_mime_type,
                    'guid' => $wpAttachment->guid,
                    'width' => $metadata['width'] ?? null,
                    'height' => $metadata['height'] ?? null,
                    'reused_existing_media_file' => true,
                ],
            ]);

            $this->reused++;
            return;
        }

        $mediaFile = MediaFile::query()->create([
            'type' => $this->detectType($wpAttachment->post_mime_type, $format),
            'visibility' => MediaFileVisibilityEnum::PUBLIC,
            'title' => $wpAttachment->post_title ?: pathinfo($normalizedPath, PATHINFO_FILENAME),
            'created_by' => null,
            'primary_variant_id' => null,
            'status' => MediaFileStatusEnum::READY,
            'processed_at' => now(),
            'created_at' => $this->normalizeDate($wpAttachment->post_date) ?? now(),
            'updated_at' => $this->normalizeDate($wpAttachment->post_modified) ?? now(),
        ]);

        $variant = MediaFileVariant::query()->create([
            'media_file_id' => $mediaFile->id,
            'disk' => MediaFileDiskEnum::WORDPRESS_UPLOADS,
            'mime_type' => $wpAttachment->post_mime_type ?: $format->mimeType(),
            'variant' => MediaFileVariantEnum::ORIGINAL,
            'format' => $format,
            'path' => $normalizedPath,
            'byte_size' => $metadata['filesize'] ?? 0,
            'width' => $metadata['width'] ?? null,
            'height' => $metadata['height'] ?? null,
            'checksum' => null,
            'generated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mediaFile->update([
            'primary_variant_id' => $variant->id,
        ]);

        WpMigrationMap::query()->create([
            'source_type' => 'wordpress_attachment',
            'source_id' => $wpAttachment->ID,
            'target_type' => MediaFile::class,
            'target_id' => $mediaFile->id,
            'meta' => [
                'attached_file' => $attachedFile,
                'alt' => $this->getMeta((int)$wpAttachment->ID, '_wp_attachment_image_alt'),
                'mime_type' => $wpAttachment->post_mime_type,
                'guid' => $wpAttachment->guid,
                'width' => $metadata['width'] ?? null,
                'height' => $metadata['height'] ?? null,
            ],
        ]);

        $this->created++;
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

    private function detectType(?string $mimeType, MediaFileFormatEnum $format): MediaFileTypeEnum
    {
        if ($mimeType && Str::startsWith($mimeType, 'image/')) {
            return MediaFileTypeEnum::IMAGE;
        }

        if ($mimeType && Str::startsWith($mimeType, 'video/')) {
            return MediaFileTypeEnum::VIDEO;
        }

        if ($mimeType && Str::startsWith($mimeType, 'audio/')) {
            return MediaFileTypeEnum::AUDIO;
        }

        if ($format === MediaFileFormatEnum::PDF) {
            return MediaFileTypeEnum::DOCUMENT;
        }

        return MediaFileTypeEnum::OTHER;
    }

    private function normalizePath(string $path): string
    {
        if (Str::startsWith($path, ['http://', 'https://'])) {
            $parsedPath = parse_url($path, PHP_URL_PATH);

            return ltrim(urldecode((string)$parsedPath), '/');
        }

        return ltrim($path, '/');
    }

    private function normalizeDate(?string $date): ?Carbon
    {
        if (!$date || str_starts_with($date, '0000-00-00')) {
            return null;
        }

        return Carbon::parse($date);
    }
}

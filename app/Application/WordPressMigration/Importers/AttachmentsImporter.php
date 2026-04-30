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

class AttachmentsImporter
{
    public function import(?callable $onProgress = null): void
    {
        DB::connection('wordpress')
            ->table('posts')
            ->where('post_type', 'attachment')
            ->orderBy('ID')
            ->chunk(300, function ($attachments) use ($onProgress) {
                foreach ($attachments as $wpAttachment) {
                    $this->importAttachment($wpAttachment);

                    if ($onProgress) {
                        $onProgress();
                    }
                }
            });
    }

    public function total(): int
    {
        return DB::connection('wordpress')
            ->table('posts')
            ->where('post_type', 'attachment')
            ->count();
    }

    private function importAttachment(object $wpAttachment): void
    {
        $exists = WpMigrationMap::query()
            ->where('source_type', 'wordpress_attachment')
            ->where('source_id', $wpAttachment->ID)
            ->where('target_type', MediaFile::class)
            ->exists();

        if ($exists) {
            return;
        }

        $attachedFile = $this->getMeta($wpAttachment->ID, '_wp_attached_file');

        if (!$attachedFile) {
            return;
        }

        $normalizedPath = $this->normalizePath($attachedFile);

        $metadata = $this->parseAttachmentMetadata(
            $this->getMeta($wpAttachment->ID, '_wp_attachment_metadata')
        );

        $existingVariant = MediaFileVariant::query()
            ->where('disk', MediaFileDiskEnum::PUBLIC)
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
                    'alt' => $this->getMeta($wpAttachment->ID, '_wp_attachment_image_alt'),
                    'mime_type' => $wpAttachment->post_mime_type,
                    'guid' => $wpAttachment->guid,
                    'width' => $metadata['width'] ?? null,
                    'height' => $metadata['height'] ?? null,
                    'reused_existing_media_file' => true,
                ],
            ]);

            return;
        }

        $format = $this->detectFormat($attachedFile);
        $type = $this->detectType($wpAttachment->post_mime_type, $format);

        $mediaFile = MediaFile::query()->create([
            'type' => $type,
            'visibility' => MediaFileVisibilityEnum::PUBLIC,
            'title' => $wpAttachment->post_title ?: pathinfo($attachedFile, PATHINFO_FILENAME),
            'created_by' => null,
            'primary_variant_id' => null,
            'status' => MediaFileStatusEnum::READY,
            'processed_at' => now(),
            'created_at' => $this->normalizeDate($wpAttachment->post_date) ?? now(),
            'updated_at' => $this->normalizeDate($wpAttachment->post_modified) ?? now(),
        ]);

        $variant = MediaFileVariant::query()->create([
            'media_file_id' => $mediaFile->id,
            'disk' => MediaFileDiskEnum::PUBLIC,
            'mime_type' => $wpAttachment->post_mime_type ?: $format?->mimeType(),
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
                'alt' => $this->getMeta($wpAttachment->ID, '_wp_attachment_image_alt'),
                'mime_type' => $wpAttachment->post_mime_type,
                'guid' => $wpAttachment->guid,
                'width' => $metadata['width'] ?? null,
                'height' => $metadata['height'] ?? null,
            ],
        ]);
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

        $data = @unserialize($value);

        return is_array($data) ? $data : [];
    }

    private function detectFormat(string $path): ?MediaFileFormatEnum
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg' => MediaFileFormatEnum::JPG,
            'jpeg' => MediaFileFormatEnum::JPEG,
            'png' => MediaFileFormatEnum::PNG,
            'webp' => MediaFileFormatEnum::WEBP,
            'gif' => MediaFileFormatEnum::GIF,
            'svg' => MediaFileFormatEnum::SVG,
            'pdf' => MediaFileFormatEnum::PDF,
            'zip' => MediaFileFormatEnum::ZIP,
            'rar' => MediaFileFormatEnum::RAR,
            'mp3' => MediaFileFormatEnum::MP3,
            'wav' => MediaFileFormatEnum::WAV,
            'mp4' => MediaFileFormatEnum::MP4,
            'woff' => MediaFileFormatEnum::WOFF,
            'woff2' => MediaFileFormatEnum::WOFF2,
            'ttf' => MediaFileFormatEnum::TTF,
            'ico' => MediaFileFormatEnum::ICO,
            'json' => MediaFileFormatEnum::JSON,
            'docx' => MediaFileFormatEnum::DOCX,
            default => null,
        };
    }

    private function detectType(?string $mimeType, ?MediaFileFormatEnum $format): MediaFileTypeEnum
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

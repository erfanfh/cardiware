<?php

namespace App\Domain\Media\Enums;

enum MediaFileFormatEnum: string
{
    case JPG = 'jpg';
    case JPEG = 'jpeg';
    case PNG = 'png';
    case WEBP = 'webp';

    case PDF = 'pdf';

    case ZIP = 'zip';

    case MP3 = 'mp3';
    case WAV = 'wav';

    case MP4 = 'mp4';

    /**
     * Return allowed formats per media type.
     *
     * @return array<int, self>
     */
    public static function allowedFor(string $mediaType): array
    {
        return match ($mediaType) {
            'image' => [
                self::JPG,
                self::JPEG,
                self::PNG,
                self::WEBP,
            ],
            'document' => [
                self::PDF,
            ],
            'archive' => [
                self::ZIP,
            ],
            'audio' => [
                self::MP3,
                self::WAV,
            ],
            'video' => [
                self::MP4,
            ],
            default => [],
        };
    }

    /**
     * Map format to default MIME type.
     */
    public function mimeType(): string
    {
        return match ($this) {
            self::JPG, self::JPEG => 'image/jpeg',
            self::PNG => 'image/png',
            self::WEBP => 'image/webp',
            self::PDF => 'application/pdf',
            self::ZIP => 'application/zip',
            self::MP3 => 'audio/mpeg',
            self::WAV => 'audio/wav',
            self::MP4 => 'video/mp4',
        };
    }
}

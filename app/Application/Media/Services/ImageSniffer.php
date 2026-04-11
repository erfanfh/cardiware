<?php

namespace App\Application\Media\Services;

use RuntimeException;

/**
 * Performs low-level inspection of raw image binaries.
 *
 * This service is responsible for *hard* validation of image files by:
 * - Detecting the real MIME type using magic bytes (`finfo`).
 * - Verifying that the binary represents a valid image structure.
 * - Extracting trusted width, height, and image type metadata.
 *
 * It is designed to be used as a security layer before any image processing
 * (e.g. re-encoding, resizing, or variant generation) to mitigate risks such as:
 * - MIME spoofing (e.g. PHP files disguised as images),
 * - Corrupted or malformed image data,
 * - Image-bomb attacks with invalid or misleading metadata.
 */
class ImageSniffer
{
    /**
     * Inspect an image binary and extract trusted metadata.
     *
     * @param string $binary
     * @return array{
     *     mime: string,
     *     width: int,
     *     height: int,
     *     image_type: int
     * }
     *
     * @throws RuntimeException If MIME detection fails or the image structure is invalid.
     */
    public function sniff(string $binary): array
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($binary);

        if (!is_string($mime) || $mime === '') {
            throw new RuntimeException('Unable to detect MIME type.');
        }

        $info = @getimagesizefromstring($binary);

        if ($info === false || empty($info[0]) || empty($info[1]) || empty($info['mime'])) {
            throw new RuntimeException('Invalid image structure (getimagesize failed).');
        }

        return [
            'mime' => $mime,
            'width' => (int)$info[0],
            'height' => (int)$info[1],
            'image_type' => (int)($info[2] ?? 0),
        ];
    }
}

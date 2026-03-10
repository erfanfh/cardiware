<?php

namespace App\Application\Media\Services;

use RuntimeException;

class WebpImageProcessor
{
    /**
     * Decode an input image binary and re-encode it as WebP.
     * This strips metadata implicitly (GD does not preserve EXIF metadata).
     *
     * @param string $binary Raw uploaded file contents
     * @param int|null $maxWidth Resize down if width exceeds this value (keeps aspect ratio)
     * @param int $quality WebP quality (0-100)
     * @return array{binary:string,width:int,height:int}
     */
    public function toWebp(string $binary, ?int $maxWidth = null, int $quality = 80): array
    {
        $img = @imagecreatefromstring($binary);
        if (!$img) {
            throw new RuntimeException('Invalid image data (decode failed).');
        }

        $srcW = imagesx($img);
        $srcH = imagesy($img);

        $dst = $img;
        $dstW = $srcW;
        $dstH = $srcH;

        if (!is_null($maxWidth) && $srcW > $maxWidth) {
            $ratio = $srcH / $srcW;
            $dstW = $maxWidth;
            $dstH = (int)round($dstW * $ratio);

            $resized = imagecreatetruecolor($dstW, $dstH);

            // Preserve transparency for PNG/WebP inputs
            imagealphablending($resized, false);
            imagesavealpha($resized, true);

            imagecopyresampled($resized, $img, 0, 0, 0, 0, $dstW, $dstH, $srcW, $srcH);

            imagedestroy($img);
            $dst = $resized;
        }

        ob_start();
        $ok = imagewebp($dst, null, max(0, min(100, $quality)));
        $webpBinary = (string)ob_get_clean();

        imagedestroy($dst);

        if (!$ok || $webpBinary === '') {
            throw new RuntimeException('Failed to encode WebP (imagewebp failed).');
        }

        return [
            'binary' => $webpBinary,
            'width' => $dstW,
            'height' => $dstH,
        ];
    }
}


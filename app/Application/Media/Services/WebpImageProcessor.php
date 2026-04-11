<?php

namespace App\Application\Media\Services;

use RuntimeException;

class WebpImageProcessor
{
    /**
     * Decode an input image binary and re-encode it as WebP.
     * Keeps aspect ratio and strips metadata by re-encoding through GD.
     *
     * @param string $binary
     * @param int|null $maxWidth
     * @param int $quality
     * @return array{binary:string,width:int,height:int}
     */
    public function toWebp(string $binary, ?int $maxWidth = null, int $quality = 80): array
    {
        $img = @imagecreatefromstring($binary);

        if (!$img) {
            throw new RuntimeException('Invalid image data (decode failed).');
        }

        $img = $this->ensureTrueColor($img);

        $srcW = imagesx($img);
        $srcH = imagesy($img);

        $dst = $img;
        $dstW = $srcW;
        $dstH = $srcH;

        if ($maxWidth !== null && $srcW > $maxWidth) {
            $ratio = $srcH / $srcW;
            $dstW = $maxWidth;
            $dstH = (int)round($dstW * $ratio);

            $resized = imagecreatetruecolor($dstW, $dstH);

            imagealphablending($resized, false);
            imagesavealpha($resized, true);

            $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
            imagefilledrectangle($resized, 0, 0, $dstW, $dstH, $transparent);

            imagecopyresampled(
                $resized,
                $img,
                0,
                0,
                0,
                0,
                $dstW,
                $dstH,
                $srcW,
                $srcH
            );

            imagedestroy($img);
            $dst = $resized;
        }

        $dst = $this->ensureTrueColor($dst);

        ob_start();
        $ok = imagewebp($dst, null, max(0, min(100, $quality)));
        $webpBinary = (string)ob_get_clean();

        imagedestroy($dst);

        if (!$ok || $webpBinary === '') {
            throw new RuntimeException('Failed to encode WebP.');
        }

        return [
            'binary' => $webpBinary,
            'width' => $dstW,
            'height' => $dstH,
        ];
    }

    /**
     * Convert palette-based GD images to truecolor so they can be encoded as WebP.
     *
     * @param \GdImage $image
     * @return \GdImage
     */
    private function ensureTrueColor(\GdImage $image): \GdImage
    {
        if (imageistruecolor($image)) {
            return $image;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        $trueColor = imagecreatetruecolor($width, $height);

        imagealphablending($trueColor, false);
        imagesavealpha($trueColor, true);

        $transparent = imagecolorallocatealpha($trueColor, 0, 0, 0, 127);
        imagefilledrectangle($trueColor, 0, 0, $width, $height, $transparent);

        imagecopy(
            $trueColor,
            $image,
            0,
            0,
            0,
            0,
            $width,
            $height
        );

        imagedestroy($image);

        return $trueColor;
    }
}

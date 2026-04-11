<?php

namespace App\Application\Media\Services;

use RuntimeException;

class ImageVariantGenerator
{
    public function __construct(
        private readonly WebpImageProcessor $processor,
    )
    {
    }

    /**
     * Generate the original WebP plus configured width-based variants.
     *
     * @param string $binary
     * @return array<string, array{binary:string,width:int,height:int}>
     */
    public function generate(string $binary): array
    {
        $qualities = config('media.images.quality', []);
        $variantsConfig = config('media.images.variants', []);

        $result = [];

        $result['original'] = $this->processor->toWebp(
            binary: $binary,
            maxWidth: null,
            quality: (int)($qualities['original'] ?? 82),
        );

        foreach ($variantsConfig as $variantKey => $options) {
            $maxWidth = (int)($options['width'] ?? 0);

            if ($maxWidth <= 0) {
                throw new RuntimeException("Invalid width configured for image variant [{$variantKey}].");
            }

            $result[$variantKey] = $this->processor->toWebp(
                binary: $binary,
                maxWidth: $maxWidth,
                quality: (int)($qualities[$variantKey] ?? 80),
            );
        }

        return $result;
    }
}

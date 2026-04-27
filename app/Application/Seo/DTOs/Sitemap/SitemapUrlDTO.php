<?php

namespace App\Application\Seo\DTOs\Sitemap;

use Carbon\CarbonInterface;

/**
 * Data transfer object for a sitemap URL entry.
 */
readonly class SitemapUrlDTO
{
    /**
     * @param array<int, SitemapImageDTO> $images
     */
    public function __construct(
        public string           $url,
        public ?CarbonInterface $lastmod = null,
        public array            $images = [],
    )
    {
    }

    public function hasImages(): bool
    {
        return count($this->images) > 0;
    }
}

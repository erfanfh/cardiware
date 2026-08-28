<?php

namespace App\Application\Seo\DTOs\Sitemap;

use Carbon\CarbonInterface;

/**
 * Data transfer object for a sitemap URL entry.
 */
readonly class SitemapUrlDTO
{
    /**
     * @param  array<int, SitemapImageDTO>  $images
     * @param  array<int, SitemapVideoDTO>  $videos
     */
    public function __construct(
        public string $url,
        public ?CarbonInterface $lastmod = null,
        public array $images = [],
        public array $videos = [],
    ) {}

    public function hasImages(): bool
    {
        return count($this->images) > 0;
    }

    public function hasVideos(): bool
    {
        return count($this->videos) > 0;
    }
}

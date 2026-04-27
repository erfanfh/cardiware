<?php

namespace App\Application\Seo\DTOs\Sitemap;

/**
 * Data transfer object for a sitemap image entry.
 */
readonly class SitemapImageDTO
{
    public function __construct(
        public string  $url,
        public ?string $title = null,
        public ?string $caption = null,
    )
    {
    }
}

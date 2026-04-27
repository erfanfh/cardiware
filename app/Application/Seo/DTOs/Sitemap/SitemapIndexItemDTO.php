<?php

namespace App\Application\Seo\DTOs\Sitemap;

use Carbon\CarbonInterface;

/**
 * Data transfer object for a sitemap index entry.
 */
readonly class SitemapIndexItemDTO
{
    public function __construct(
        public string           $url,
        public ?CarbonInterface $lastmod = null,
    )
    {
    }
}

<?php

namespace App\Application\Seo\DTOs\Sitemap;

use Carbon\CarbonInterface;

readonly class SitemapVideoDTO
{
    public function __construct(
        public string $thumbnailUrl,
        public string $title,
        public string $description,
        public ?string $contentUrl = null,
        public ?string $playerUrl = null,
        public ?int $duration = null,
        public ?CarbonInterface $publicationDate = null,
        public ?int $viewCount = null,
    ) {}
}

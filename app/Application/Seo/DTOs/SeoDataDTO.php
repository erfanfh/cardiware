<?php

namespace App\Application\Seo\DTOs;

readonly class SeoDataDTO
{
    public function __construct(
        public ?string           $title = null,
        public ?string           $description = null,
        public ?string           $canonicalUrl = null,
        public ?OpenGraphDataDTO $openGraph = null,
    )
    {
    }
}

<?php

namespace App\Application\Seo\DTOs;

readonly class SeoDataDTO
{
    public function __construct(
        public ?string                  $title = null,
        public ?string                  $description = null,
        public ?string                  $robots = null,
        public ?string                  $canonicalUrl = null,
        public ?string                  $nextUrl = null,
        public ?string                  $prevUrl = null,
        public ?OpenGraphDataDTO        $openGraph = null,
        public ?TwitterCardDataDTO      $twitter = null,
        public ?VerificationMetaDataDTO $verification = null,
        public ?string                  $schemaJson = null,
    )
    {
    }

    public function hasSchema(): bool
    {
        return filled($this->schemaJson);
    }
}

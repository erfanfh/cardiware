<?php

namespace App\Application\Seo\DTOs;

final class ResolvedPageSeoPayloadDTO
{
    public function __construct(
        public readonly string  $canonicalUrl,
        public readonly ?string $robots = null,

        public readonly ?string $title = null,
        public readonly ?string $description = null,
        public readonly ?string $headline = null,

        public readonly ?string $ogTitle = null,
        public readonly ?string $ogDescription = null,
        public readonly array   $ogImage = [],

        public readonly ?string $xTitle = null,
        public readonly ?string $xDescription = null,
        public readonly ?string $xImageUrl = null,

        public readonly ?string $pageImageUrl = null,
        public readonly ?int    $pageImageWidth = null,
        public readonly ?int    $pageImageHeight = null,
        public readonly ?string $pageImageCaption = null,

        public readonly ?string $readingTimeLabel = null,
    )
    {
    }
}

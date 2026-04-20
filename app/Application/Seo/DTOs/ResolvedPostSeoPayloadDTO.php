<?php

namespace App\Application\Seo\DTOs;

final class ResolvedPostSeoPayloadDTO
{
    /**
     * @param array<string, mixed> $ogImage
     * @param array<int, string> $keywords
     * @param array<int, string> $articleSections
     * @param array<int, string> $authorSameAs
     */
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

        public readonly ?string $publishedTime = null,
        public readonly ?string $modifiedTime = null,

        public readonly ?string $authorName = null,
        public readonly ?string $authorUrl = null,
        public readonly ?string $authorIdentifier = null,
        public readonly array   $authorSameAs = [],

        public readonly ?string $pageImageUrl = null,
        public readonly ?int    $pageImageWidth = null,
        public readonly ?int    $pageImageHeight = null,
        public readonly ?string $pageImageCaption = null,

        public readonly ?int    $wordCount = null,
        public readonly ?int    $commentCount = null,
        public readonly array   $keywords = [],
        public readonly array   $articleSections = [],

        public readonly ?string $readingTimeLabel = null,
    )
    {
    }
}

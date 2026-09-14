<?php

namespace App\Application\Seo\DTOs;

final class SchemaContextDTO
{
    /**
     * @param array<int, string> $organizationSameAs
     * @param array<int, string> $authorSameAs
     * @param array<int, string> $keywords
     * @param array<int, string> $articleSections
     */
    public function __construct(
        public readonly string  $pageUrl,
        public readonly ?string $pageTitle = null,
        public readonly ?string $pageDescription = null,
        public readonly ?string $pageType = null,

        public readonly string  $siteUrl = '',
        public readonly ?string $siteName = null,
        public readonly ?string $siteAlternateName = null,
        public readonly ?string $siteDescription = null,
        public readonly string  $inLanguage = 'fa-IR',

        public readonly ?string $organizationName = null,
        public readonly ?string $organizationAlternateName = null,
        public readonly ?string $organizationLegalName = null,
        public readonly ?string $organizationEmail = null,
        public readonly ?string $organizationTelephone = null,
        public readonly ?string $organizationFoundingDate = null,
        public readonly array   $organizationSameAs = [],
        public readonly ?string $organizationNumberOfEmployees = null,

        public readonly ?string $logoUrl = null,
        public readonly ?int    $logoWidth = null,
        public readonly ?int    $logoHeight = null,
        public readonly ?string $logoCaption = null,

        public readonly ?string $pageImageUrl = null,
        public readonly ?int    $pageImageWidth = null,
        public readonly ?int    $pageImageHeight = null,
        public readonly ?string $pageImageCaption = null,
        public readonly ?string $pageImageSchemaId = null,

        public readonly ?string $headline = null,
        public readonly ?string $publishedTime = null,
        public readonly ?string $modifiedTime = null,
        public readonly ?int    $wordCount = null,
        public readonly ?int    $commentCount = null,
        public readonly array   $keywords = [],
        public readonly array   $articleSections = [],
        public readonly ?string $commentUrl = null,

        public readonly ?string $authorName = null,
        public readonly ?string $authorUrl = null,
        public readonly ?string $authorIdentifier = null,
        public readonly array   $authorSameAs = [],

        public readonly ?float  $averageRating = null,
        public readonly int     $ratingCount = 0,
    )
    {
    }
}

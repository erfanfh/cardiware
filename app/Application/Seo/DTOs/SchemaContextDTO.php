<?php

namespace App\Application\Seo\DTOs;

final class SchemaContextDTO
{
    /**
     * @param array<int, string> $organizationSameAs
     */
    public function __construct(
        public readonly string  $pageUrl,
        public readonly string  $pageTitle,
        public readonly ?string $pageDescription,
        public readonly string  $siteUrl,
        public readonly string  $siteName,
        public readonly ?string $siteAlternateName,
        public readonly ?string $siteDescription,
        public readonly string  $inLanguage,
        public readonly string  $pageType,
        public readonly ?string $organizationName,
        public readonly ?string $organizationAlternateName,
        public readonly ?string $organizationLegalName,
        public readonly ?string $organizationEmail,
        public readonly ?string $organizationTelephone,
        public readonly ?string $organizationFoundingDate,
        public readonly array   $organizationSameAs,
        public readonly ?string $organizationNumberOfEmployees,
        public readonly ?string $logoUrl,
        public readonly ?int    $logoWidth,
        public readonly ?int    $logoHeight,
        public readonly ?string $logoCaption,
    )
    {
    }
}

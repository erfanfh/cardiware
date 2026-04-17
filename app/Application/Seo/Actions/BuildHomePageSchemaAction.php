<?php

namespace App\Application\Seo\Actions;

use App\Application\Seo\DTOs\SchemaNodeDTO;

class BuildHomePageSchemaAction
{
    /**
     * @return array<int, SchemaNodeDTO>
     */
    public function execute(
        string  $canonicalUrl,
        ?string $title,
        ?string $description,
    ): array
    {
        $siteName = get_setting('seo', 'site_name') ?: get_setting('general', 'title') ?: config('app.name');

        $organizationName = get_setting('seo', 'organization_name') ?: $siteName;

        $organizationAlternateName = get_setting('seo', 'organization_alternate_name') ?: null;

        $organizationUrl = get_setting('seo', 'organization_url') ?: $canonicalUrl;

        $organizationLogo = get_setting('seo', 'organization_logo') ?: null;
        $organizationEmail = get_setting('seo', 'organization_email') ?: null;
        $organizationPhone = get_setting('seo', 'organization_phone') ?: null;

        $sameAs = $this->resolveSameAs(
            get_setting('seo', 'organization_same_as')
        );

        $organizationId = rtrim($canonicalUrl, '/') . '/#organization';
        $websiteId = rtrim($canonicalUrl, '/') . '/#website';
        $webpageId = $canonicalUrl;
        $breadcrumbId = rtrim($canonicalUrl, '/') . '/#breadcrumb';

        $organization = array_filter([
            '@type' => 'Organization',
            '@id' => $organizationId,
            'name' => $organizationName,
            'alternateName' => $organizationAlternateName,
            'url' => $organizationUrl,
            'email' => $organizationEmail,
            'telephone' => $organizationPhone,
            'sameAs' => $sameAs !== [] ? $sameAs : null,
            'logo' => $organizationLogo
                ? [
                    '@type' => 'ImageObject',
                    'url' => $organizationLogo,
                ]
                : null,
        ], fn($value) => !is_null($value) && $value !== '');

        $website = array_filter([
            '@type' => 'WebSite',
            '@id' => $websiteId,
            'url' => $canonicalUrl,
            'name' => $siteName,
            'description' => $description,
            'publisher' => [
                '@id' => $organizationId,
            ],
            'inLanguage' => 'fa-IR',
        ], fn($value) => !is_null($value) && $value !== '');

        $collectionPage = array_filter([
            '@type' => 'CollectionPage',
            '@id' => $webpageId,
            'url' => $canonicalUrl,
            'name' => $title,
            'description' => $description,
            'isPartOf' => [
                '@id' => $websiteId,
            ],
            'about' => [
                '@id' => $organizationId,
            ],
            'breadcrumb' => [
                '@id' => $breadcrumbId,
            ],
            'inLanguage' => 'fa-IR',
        ], fn($value) => !is_null($value) && $value !== '');

        $breadcrumb = [
            '@type' => 'BreadcrumbList',
            '@id' => $breadcrumbId,
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'خانه',
                    'item' => $canonicalUrl,
                ],
            ],
        ];

        return [
            new SchemaNodeDTO($collectionPage),
            new SchemaNodeDTO($breadcrumb),
            new SchemaNodeDTO($website),
            new SchemaNodeDTO($organization),
        ];
    }

    /**
     * @return array<int, string>
     */
    private function resolveSameAs(mixed $value): array
    {
        if (!is_string($value) || blank($value)) {
            return [];
        }

        return collect(preg_split('/\r\n|\r|\n/', $value) ?: [])
            ->map(fn(?string $item) => trim((string)$item))
            ->filter(fn(string $item) => filled($item))
            ->values()
            ->all();
    }
}

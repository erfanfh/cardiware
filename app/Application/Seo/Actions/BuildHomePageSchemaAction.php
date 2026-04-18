<?php

namespace App\Application\Seo\Actions;

class BuildHomePageSchemaAction
{
    public function __construct(
        private readonly BuildOrganizationSchemaNodeAction $buildOrganizationSchemaNodeAction,
        private readonly BuildWebsiteSchemaNodeAction      $buildWebsiteSchemaNodeAction,
    )
    {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function execute(
        string  $canonicalUrl,
        ?string $title,
        ?string $description,
    ): array
    {
        $organization = $this->buildOrganizationSchemaNodeAction->execute();
        $website = $this->buildWebsiteSchemaNodeAction->execute($description);

        $organizationId = $organization['@id'] ?? null;
        $websiteId = $website['@id'] ?? null;
        $breadcrumbId = rtrim($canonicalUrl, '/') . '/#breadcrumb';
        $webPageId = $canonicalUrl;

        $collectionPage = array_filter([
            '@type' => 'CollectionPage',
            '@id' => $webPageId,
            'url' => $canonicalUrl,
            'name' => $title,
            'description' => $description,
            'isPartOf' => $websiteId ? ['@id' => $websiteId] : null,
            'about' => $organizationId ? ['@id' => $organizationId] : null,
            'breadcrumb' => ['@id' => $breadcrumbId],
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
            $collectionPage,
            $breadcrumb,
            $website,
            $organization,
        ];
    }
}

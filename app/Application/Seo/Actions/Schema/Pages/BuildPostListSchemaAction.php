<?php

namespace App\Application\Seo\Actions\Schema\Pages;

use App\Application\Seo\DTOs\SchemaContextDTO;

final class BuildPostListSchemaAction
{
    /**
     * @param array<string, mixed> $websiteNode
     * @param array<string, mixed> $organizationNode
     * @param array<string, mixed> $breadcrumbNode
     * @return array<string, mixed>
     */
    public function execute(
        SchemaContextDTO $context,
        array            $websiteNode = [],
        array            $organizationNode = [],
        array            $breadcrumbNode = [],
    ): array
    {
        $websiteId = $websiteNode['@id'] ?? null;
        $organizationId = $organizationNode['@id'] ?? null;
        $breadcrumbId = $breadcrumbNode['@id'] ?? null;
        $primaryImageId = $context->pageImageSchemaId;

        return [
            '@type' => 'CollectionPage',
            '@id' => $context->pageUrl,
            'url' => $context->pageUrl,
            'name' => $context->pageTitle,
            'description' => $context->pageDescription,
            'isPartOf' => $websiteId ? [
                '@id' => $websiteId,
            ] : null,
            'publisher' => $organizationId ? [
                '@id' => $organizationId,
            ] : null,
            'primaryImageOfPage' => $primaryImageId ? [
                '@id' => $primaryImageId,
            ] : null,
            'image' => $primaryImageId ? [
                '@id' => $primaryImageId,
            ] : null,
            'thumbnailUrl' => $context->pageImageUrl,
            'breadcrumb' => $breadcrumbId ? [
                '@id' => $breadcrumbId,
            ] : null,
            'inLanguage' => $context->inLanguage,
            'potentialAction' => [
                [
                    '@type' => 'ReadAction',
                    'target' => [$context->pageUrl],
                ],
            ],
        ];
    }
}

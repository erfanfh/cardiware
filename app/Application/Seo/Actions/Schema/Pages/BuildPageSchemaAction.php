<?php

namespace App\Application\Seo\Actions\Schema\Pages;

use App\Application\Seo\DTOs\SchemaContextDTO;

final class BuildPageSchemaAction
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
        $breadcrumbId = $breadcrumbNode['@id'] ?? null;

        return [
            '@type' => 'WebPage',
            '@id' => $context->pageUrl,
            'url' => $context->pageUrl,
            'name' => $context->pageTitle,
            'isPartOf' => $websiteId ? [
                '@id' => $websiteId,
            ] : null,
            'thumbnailUrl' => $context->pageImageUrl,
            'description' => $context->pageDescription,
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

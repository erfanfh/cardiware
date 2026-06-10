<?php

namespace App\Application\Seo\Actions\Schema\Pages;

use App\Application\Seo\DTOs\ProductSchemaContextDTO;

final class BuildProductPageSchemaAction
{
    /**
     * @param array<string, mixed> $websiteNode
     * @param array<string, mixed> $breadcrumbNode
     * @param array<string, mixed> $productNode
     * @return array<string, mixed>
     */
    public function execute(
        ProductSchemaContextDTO $context,
        array                   $websiteNode = [],
        array                   $breadcrumbNode = [],
        array                   $productNode = [],
    ): array
    {
        $websiteId = $websiteNode['@id'] ?? null;
        $breadcrumbId = $breadcrumbNode['@id'] ?? null;
        $productId = $productNode['@id'] ?? null;
        $primaryImageId = $context->base->pageImageSchemaId;

        return [
            '@type' => 'ItemPage',
            '@id' => $context->base->pageUrl,
            'url' => $context->base->pageUrl,
            'name' => $context->base->pageTitle,
            'description' => $context->base->pageDescription,
            'isPartOf' => $websiteId ? [
                '@id' => $websiteId,
            ] : null,
            'primaryImageOfPage' => $primaryImageId ? [
                '@id' => $primaryImageId,
            ] : null,
            'image' => $primaryImageId ? [
                '@id' => $primaryImageId,
            ] : null,
            'thumbnailUrl' => $context->base->pageImageUrl,
            'datePublished' => $context->base->publishedTime,
            'dateModified' => $context->base->modifiedTime,
            'breadcrumb' => $breadcrumbId ? [
                '@id' => $breadcrumbId,
            ] : null,
            'mainEntity' => $productId ? [
                '@id' => $productId,
            ] : null,
            'inLanguage' => $context->base->inLanguage,
            'potentialAction' => [
                [
                    '@type' => 'ViewAction',
                    'target' => [$context->base->pageUrl],
                ],
            ],
        ];
    }
}

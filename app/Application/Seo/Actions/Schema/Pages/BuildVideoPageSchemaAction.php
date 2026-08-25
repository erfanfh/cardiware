<?php

namespace App\Application\Seo\Actions\Schema\Pages;

use App\Application\Seo\DTOs\SchemaContextDTO;

final class BuildVideoPageSchemaAction
{
    /**
     * @param  array<string, mixed>  $websiteNode
     * @param  array<string, mixed>  $breadcrumbNode
     * @param  array<string, mixed>  $videoNode
     * @return array<string, mixed>
     */
    public function execute(
        SchemaContextDTO $context,
        array $websiteNode = [],
        array $breadcrumbNode = [],
        array $videoNode = [],
    ): array {
        $imageId = $context->pageImageSchemaId;

        return [
            '@type' => 'WebPage',
            '@id' => $context->pageUrl,
            'url' => $context->pageUrl,
            'name' => $context->pageTitle,
            'description' => $context->pageDescription,
            'isPartOf' => isset($websiteNode['@id']) ? ['@id' => $websiteNode['@id']] : null,
            'mainEntity' => isset($videoNode['@id']) ? ['@id' => $videoNode['@id']] : null,
            'primaryImageOfPage' => $imageId ? ['@id' => $imageId] : null,
            'image' => $imageId ? ['@id' => $imageId] : null,
            'thumbnailUrl' => $context->pageImageUrl,
            'datePublished' => $context->publishedTime,
            'dateModified' => $context->modifiedTime,
            'breadcrumb' => isset($breadcrumbNode['@id']) ? ['@id' => $breadcrumbNode['@id']] : null,
            'inLanguage' => $context->inLanguage,
            'potentialAction' => $videoNode ? [[
                '@type' => 'WatchAction',
                'target' => [$context->pageUrl],
            ]] : null,
        ];
    }
}

<?php

namespace App\Application\Seo\Actions\Schema\Pages;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;

final class BuildStoreSchemaAction
{
    public function __construct(
        protected SchemaIdGenerator $schemaIdGenerator,
    )
    {
    }

    /**
     * @param array<string, mixed> $websiteNode
     * @param array<string, mixed> $organizationNode
     * @param array<string, mixed> $breadcrumbNode
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

        $storeId = $this->schemaIdGenerator->custom($context->pageUrl, '#store');

        return [
            '@type' => 'WebPage',
            '@id' => $context->pageUrl,

            'url' => $context->pageUrl,
            'name' => $context->pageTitle,
            'description' => $context->pageDescription,

            'isPartOf' => $websiteId ? ['@id' => $websiteId] : null,

            'about' => ['@id' => $storeId],
            'mainEntity' => ['@id' => $storeId],

            'primaryImageOfPage' => $context->pageImageUrl ? [
                '@id' => $this->schemaIdGenerator->primaryImage($context->pageUrl),
            ] : null,

            'image' => $context->pageImageUrl ? [
                '@id' => $this->schemaIdGenerator->primaryImage($context->pageUrl),
            ] : null,

            'thumbnailUrl' => $context->pageImageUrl,

            'datePublished' => $context->publishedTime,
            'dateModified' => $context->modifiedTime,

            'breadcrumb' => $breadcrumbId ? ['@id' => $breadcrumbId] : null,

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

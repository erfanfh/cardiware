<?php

namespace App\Application\Seo\Actions\Schema\Pages;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;

final class BuildAuthorSchemaAction
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
        $primaryImageId = $context->pageImageSchemaId;

        $authorUrl = rtrim((string)$context->authorUrl, '/') . '/';
        $personId = $this->schemaIdGenerator->custom($authorUrl, '#person');

        return [
            '@type' => 'ProfilePage',
            '@id' => $context->pageUrl,
            'url' => $context->pageUrl,
            'name' => $context->pageTitle,
            'description' => $context->pageDescription,
            'isPartOf' => $websiteId ? [
                '@id' => $websiteId,
            ] : null,
            'mainEntity' => [
                '@id' => $personId,
            ],
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

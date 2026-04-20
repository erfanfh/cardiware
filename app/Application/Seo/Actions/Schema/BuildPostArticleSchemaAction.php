<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;

final class BuildPostArticleSchemaAction
{
    public function __construct(
        protected SchemaIdGenerator $schemaIdGenerator,
    )
    {
    }

    /**
     * @param array<string, mixed> $organizationNode
     * @param array<string, mixed> $personNode
     * @param array<string, mixed> $primaryImageNode
     * @return array<string, mixed>
     */
    public function execute(
        SchemaContextDTO $context,
        array            $organizationNode = [],
        array            $personNode = [],
        array            $primaryImageNode = [],
    ): array
    {
        $organizationId = $organizationNode['@id'] ?? null;
        $personId = $personNode['@id'] ?? null;
        $primaryImageId = $primaryImageNode['@id'] ?? null;

        return [
            '@type' => 'Article',
            '@id' => $this->schemaIdGenerator->article($context->pageUrl),
            'isPartOf' => [
                '@id' => $context->pageUrl,
            ],
            'author' => $personId ? [
                'name' => $context->authorName,
                '@id' => $personId,
            ] : ($context->authorName ? [
                'name' => $context->authorName,
            ] : null),
            'headline' => $context->headline ?: $context->pageTitle,
            'datePublished' => $context->publishedTime,
            'dateModified' => $context->modifiedTime,
            'mainEntityOfPage' => [
                '@id' => $context->pageUrl,
            ],
            'wordCount' => $context->wordCount,
            'commentCount' => $context->commentCount,
            'publisher' => $organizationId ? [
                '@id' => $organizationId,
            ] : null,
            'image' => $primaryImageId ? [
                '@id' => $primaryImageId,
            ] : null,
            'thumbnailUrl' => $context->pageImageUrl,
            'keywords' => $context->keywords,
            'articleSection' => $context->articleSections,
            'inLanguage' => $context->inLanguage,
            'potentialAction' => [
                [
                    '@type' => 'CommentAction',
                    'name' => 'Comment',
                    'target' => [$context->commentUrl ?: $context->pageUrl . '#respond'],
                ],
            ],
        ];
    }
}

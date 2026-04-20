<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;

final class BuildWebsiteSchemaAction
{
    public function __construct(
        protected SchemaIdGenerator $schemaIdGenerator,
    )
    {
    }

    /**
     * @param array<string, mixed> $organizationNode
     * @param array<string, mixed> $searchActionNode
     * @return array<string, mixed>
     */
    public function execute(
        SchemaContextDTO $context,
        array            $organizationNode = [],
        array            $searchActionNode = [],
    ): array
    {
        $organizationId = $organizationNode['@id'] ?? null;

        return [
            '@type' => 'WebSite',
            '@id' => $this->schemaIdGenerator->website($context->siteUrl),
            'url' => $context->siteUrl,
            'name' => $context->siteName,
            'description' => $context->siteDescription,
            'alternateName' => $context->siteAlternateName,
            'publisher' => $organizationId ? [
                '@id' => $organizationId,
            ] : null,
            'potentialAction' => $searchActionNode !== [] ? [$searchActionNode] : null,
            'inLanguage' => $context->inLanguage,
        ];
    }
}

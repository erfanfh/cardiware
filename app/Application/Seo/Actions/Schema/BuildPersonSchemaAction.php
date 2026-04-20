<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;

final class BuildPersonSchemaAction
{
    public function __construct(
        protected SchemaIdGenerator $schemaIdGenerator,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(SchemaContextDTO $context): array
    {
        if (blank($context->authorName)) {
            return [];
        }

        return [
            '@type' => 'Person',
            '@id' => $this->schemaIdGenerator->person($context->siteUrl, $context->authorIdentifier ?: $context->authorName),
            'name' => $context->authorName,
            'sameAs' => $context->authorSameAs,
            'url' => $context->authorUrl,
        ];
    }
}

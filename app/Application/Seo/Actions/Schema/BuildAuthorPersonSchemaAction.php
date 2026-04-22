<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;
use App\Domain\Identity\Models\User;

final class BuildAuthorPersonSchemaAction
{
    public function __construct(
        protected SchemaIdGenerator $schemaIdGenerator,
    )
    {
    }

    /**
     * @param array<string, mixed> $pageNode
     * @return array<string, mixed>
     */
    public function execute(
        User             $author,
        SchemaContextDTO $context,
        array            $pageNode = [],
    ): array
    {
        $personId = $this->schemaIdGenerator->custom($context->pageUrl, '#person');
        $pageId = $pageNode['@id'] ?? $context->pageUrl;

        return [
            '@type' => 'Person',
            '@id' => $personId,
            'name' => $context->authorName,
            'url' => $context->authorUrl,
            'image' => $context->pageImageSchemaId ? [
                '@id' => $context->pageImageSchemaId,
            ] : null,
            'sameAs' => $context->authorSameAs,
            'mainEntityOfPage' => [
                '@id' => $pageId,
            ],
        ];
    }
}

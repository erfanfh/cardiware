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
     * @param User $author
     * @param SchemaContextDTO $context
     * @return array<string, mixed>
     */
    public function execute(
        User             $author,
        SchemaContextDTO $context,
    ): array
    {
        $authorUrl = rtrim((string)$context->authorUrl, '/') . '/';

        $personId = $this->schemaIdGenerator->custom($authorUrl, '#person');

        return [
            '@type' => 'Person',
            '@id' => $personId,
            'name' => $context->authorName,
            'url' => $authorUrl,
            'image' => $context->pageImageSchemaId ? [
                '@id' => $context->pageImageSchemaId,
            ] : null,
            'sameAs' => $context->authorSameAs,
            'mainEntityOfPage' => [
                '@id' => $authorUrl,
            ],
        ];
    }
}

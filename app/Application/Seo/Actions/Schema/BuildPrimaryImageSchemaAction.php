<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;

final class BuildPrimaryImageSchemaAction
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
        if (blank($context->pageImageUrl)) {
            return [];
        }

        return [
            '@type' => 'ImageObject',
            '@id' => $this->schemaIdGenerator->primaryImage($context->pageUrl),
            'inLanguage' => $context->inLanguage,
            'url' => $context->pageImageUrl,
            'contentUrl' => $context->pageImageUrl,
            'width' => $context->pageImageWidth,
            'height' => $context->pageImageHeight,
            'caption' => $context->pageImageCaption ?: $context->pageTitle,
        ];
    }
}

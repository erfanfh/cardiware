<?php

namespace App\Application\Seo\Actions\Schema;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;

final class BuildLogoImageSchemaAction
{
    public function __construct(
        protected SchemaIdGenerator $schemaIdGenerator,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function handle(SchemaContextDTO $context): array
    {
        if (blank($context->logoUrl)) {
            return [];
        }

        return [
            '@type' => 'ImageObject',
            '@id' => $this->schemaIdGenerator->logoImage($context->siteUrl),
            'inLanguage' => $context->inLanguage,
            'url' => $context->logoUrl,
            'contentUrl' => $context->logoUrl,
            'width' => $context->logoWidth,
            'height' => $context->logoHeight,
            'caption' => $context->logoCaption ?: $context->organizationName ?: $context->siteName,
        ];
    }
}

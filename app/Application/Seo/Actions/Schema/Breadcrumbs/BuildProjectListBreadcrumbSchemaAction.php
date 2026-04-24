<?php

namespace App\Application\Seo\Actions\Schema\Breadcrumbs;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;

final class BuildProjectListBreadcrumbSchemaAction
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
        return [
            '@type' => 'BreadcrumbList',
            '@id' => $this->schemaIdGenerator->breadcrumb($context->pageUrl),
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'خانه',
                    'item' => $context->siteUrl,
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $context->headline ?: 'پروژه ها',
                ],
            ],
        ];
    }
}

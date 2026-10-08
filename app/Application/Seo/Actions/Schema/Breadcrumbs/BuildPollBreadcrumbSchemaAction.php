<?php

namespace App\Application\Seo\Actions\Schema\Breadcrumbs;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;

final class BuildPollBreadcrumbSchemaAction
{
    public function __construct(private readonly SchemaIdGenerator $ids) {}

    /** @return array<string, mixed> */
    public function execute(SchemaContextDTO $context): array
    {
        return [
            '@type' => 'BreadcrumbList',
            '@id' => $this->ids->breadcrumb($context->pageUrl),
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
                    'name' => 'نظرسنجی‌ها',
                    'item' => route('polls.index'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 3,
                    'name' => $context->headline,
                ],
            ],
        ];
    }
}

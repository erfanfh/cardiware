<?php

namespace App\Application\Seo\Actions\Schema\Breadcrumbs;

use App\Application\Seo\DTOs\ProductSchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;

final class BuildProductBreadcrumbSchemaAction
{
    public function __construct(
        protected SchemaIdGenerator $schemaIdGenerator,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(ProductSchemaContextDTO $context): array
    {
        $items = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'خانه',
                'item' => $context->base->siteUrl,
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'فروشگاه',
                'item' => front_route('products.list'),
            ],
        ];

        $position = 3;

        if (filled($context->categoryName)) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $context->categoryName,
                'item' => $context->categoryUrl,
            ];
        }

        $items[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $context->productName,
        ];

        return [
            '@type' => 'BreadcrumbList',
            '@id' => $this->schemaIdGenerator->breadcrumb($context->base->pageUrl),
            'itemListElement' => $items,
        ];
    }
}

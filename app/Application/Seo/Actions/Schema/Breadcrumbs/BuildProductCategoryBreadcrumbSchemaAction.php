<?php

namespace App\Application\Seo\Actions\Schema\Breadcrumbs;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;
use App\Domain\Taxonomy\Models\TaxonomyTerm;

final class BuildProductCategoryBreadcrumbSchemaAction
{
    public function __construct(
        private readonly SchemaIdGenerator $schemaIdGenerator,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(SchemaContextDTO $context, TaxonomyTerm $category): array
    {
        $items = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'خانه',
                'item' => $context->siteUrl,
            ],
            [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => 'محصولات',
                'item' => front_route('products.list'),
            ],
        ];

        foreach ($this->lineage($category) as $index => $term) {
            $isCurrent = $term->is($category);
            $item = [
                '@type' => 'ListItem',
                'position' => $index + 3,
                'name' => $term->title,
            ];

            if (! $isCurrent) {
                $item['item'] = front_route('products.categories', ['path' => $term->path]);
            }

            $items[] = $item;
        }

        return [
            '@type' => 'BreadcrumbList',
            '@id' => $this->schemaIdGenerator->breadcrumb($context->pageUrl),
            'itemListElement' => $items,
        ];
    }

    /**
     * @return array<int, TaxonomyTerm>
     */
    private function lineage(TaxonomyTerm $category): array
    {
        $lineage = [];
        $current = $category;

        while ($current instanceof TaxonomyTerm) {
            $lineage[] = $current;
            $current->loadMissing('parent');
            $current = $current->parent;
        }

        return array_reverse($lineage);
    }
}

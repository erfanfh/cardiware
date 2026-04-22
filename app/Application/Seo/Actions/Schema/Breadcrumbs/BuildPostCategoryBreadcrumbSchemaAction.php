<?php

namespace App\Application\Seo\Actions\Schema\Breadcrumbs;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;
use App\Domain\Taxonomy\Models\TaxonomyTerm;

final class BuildPostCategoryBreadcrumbSchemaAction
{
    public function __construct(
        protected SchemaIdGenerator $schemaIdGenerator,
    )
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function execute(SchemaContextDTO $context, TaxonomyTerm $category): array
    {
        $categories = $this->resolveCategoryLineage($category);

        $itemListElement = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'خانه',
                'item' => $context->siteUrl,
            ],
        ];

        $position = 2;

        foreach ($categories as $index => $item) {
            $isLast = $index === array_key_last($categories);

            $listItem = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => (string)$item->title,
            ];

            if (!$isLast) {
                $listItem['item'] = route('posts.categories', ['path' => $item->path]) . '/';
            }

            $itemListElement[] = $listItem;
            $position++;
        }

        return [
            '@type' => 'BreadcrumbList',
            '@id' => $this->schemaIdGenerator->breadcrumb($context->pageUrl),
            'itemListElement' => $itemListElement,
        ];
    }

    /**
     * دسته‌ها را از ریشه تا دسته فعلی برمی‌گرداند.
     *
     * @return array<int, TaxonomyTerm>
     */
    protected function resolveCategoryLineage(TaxonomyTerm $category): array
    {
        $lineage = [];
        $current = $category;

        while ($current) {
            $lineage[] = $current;

            if (!$current->relationLoaded('parent')) {
                $current->load('parent');
            }

            $parent = $current->parent;

            if (!$parent instanceof TaxonomyTerm) {
                break;
            }

            $current = $parent;
        }

        return array_reverse($lineage);
    }
}

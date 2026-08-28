<?php

namespace App\Application\Seo\Actions\Schema\Breadcrumbs;

use App\Application\Seo\DTOs\SchemaContextDTO;
use App\Application\Seo\Support\SchemaIdGenerator;
use App\Domain\Video\Models\Video;

final class BuildVideoBreadcrumbSchemaAction
{
    public function __construct(private readonly SchemaIdGenerator $ids) {}

    /** @return array<string, mixed> */
    public function execute(Video $video, SchemaContextDTO $context): array
    {
        $items = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'خانه', 'item' => $context->siteUrl],
            ['@type' => 'ListItem', 'position' => 2, 'name' => 'ویدیوها', 'item' => route('videos.index')],
        ];

        $category = $video->categories->first();
        if ($category) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => count($items) + 1,
                'name' => $category->title,
                'item' => route($category->taxonomy->archiveRouteName(), $category->path),
            ];
        }

        $items[] = [
            '@type' => 'ListItem',
            'position' => count($items) + 1,
            'name' => $context->headline,
        ];

        return [
            '@type' => 'BreadcrumbList',
            '@id' => $this->ids->breadcrumb($context->pageUrl),
            'itemListElement' => $items,
        ];
    }
}

<?php

namespace App\Application\System\Actions\Sidebar;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetSidebarCategoriesAction
{
    /**
     * @return array{
     *     title:string,
     *     subtitle:string,
     *     categories:Collection<int, TaxonomyTerm>
     * }
     */
    public function execute(): array
    {
        return Cache::tags([
            CacheTag::SIDEBAR,
            CacheTag::SIDEBAR_CATEGORIES,
            CacheTag::CATEGORIES,
        ])->remember(
            CacheKey::sidebarCategories(),
            CacheTtl::medium(),
            function () {
                $items = collect(get_setting('sidebarWidgets', 'categories_items') ?? []);

                $categoryIds = $items
                    ->pluck('category_id')
                    ->filter()
                    ->unique()
                    ->values();

                $categories = TaxonomyTerm::query()
                    ->forTaxonomy(TaxonomyKeyEnum::POST_CATEGORY)
                    ->whereIn('id', $categoryIds)
                    ->select(['id', 'title', 'path'])
                    ->withCount('posts')
                    ->get()
                    ->keyBy('id');

                $orderedCategories = $items
                    ->map(fn($item) => $categories->get($item['category_id'] ?? null))
                    ->filter()
                    ->values();

                return [
                    'title' => (string)get_setting('sidebarWidgets', 'categories_title'),
                    'subtitle' => (string)get_setting('sidebarWidgets', 'categories_subtitle'),
                    'categories' => $orderedCategories,
                ];
            }
        );
    }
}

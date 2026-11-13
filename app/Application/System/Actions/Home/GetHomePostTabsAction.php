<?php

namespace App\Application\System\Actions\Home;

use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Collection;

class GetHomePostTabsAction
{
    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function execute(): Collection
    {
        return collect(get_setting('home', 'posts_items') ?? [])
            ->map(function (array $item, int $index) {
                $mainCategoryIds = collect($item['main_category_ids'] ?? [])
                    ->filter()
                    ->map(fn($id) => (int)$id)
                    ->values();

                $categoryIds = app(GetHomeFreePostsTabDataAction::class)
                    ->resolveCategoryIdsWithDescendants($mainCategoryIds->all());

                return [
                    'key' => 'tab-' . ($index + 1),

                    'main_title' => $item['main_title'] ?? '',
                    'main_count' => max(1, (int)($item['main_count'] ?? 11)),
                    'hero_count' => min(12, max(3, (int)($item['hero_count'] ?? 3))),
                    'list_count' => min(30, max(0, (int)($item['list_count'] ?? 8))),
                    'main_category_ids' => $mainCategoryIds->all(),
                    'main_category' => $mainCategoryIds->isNotEmpty()
                        ? TaxonomyTerm::query()->find($mainCategoryIds->first())
                        : null,
                    'today_count' => $categoryIds !== []
                        ? (int)Post::query()
                            ->where('status', PostStatusEnum::ACTIVE)
                            ->where('published_at', '>=', now()->startOfDay())
                            ->where('published_at', '<=', now())
                            ->whereHas('categories', fn($query) => $query->whereIn('taxonomy_terms.id', $categoryIds))
                            ->count()
                        : 0,

                    'list_box_1_title' => $item['list_box_1_title'] ?? '',
                    'list_box_1_count' => max(1, (int)($item['list_box_1_count'] ?? 10)),
                    'list_box_1_category_ids' => collect($item['list_box_1_category_ids'] ?? [])
                        ->filter()
                        ->map(fn($id) => (int)$id)
                        ->values()
                        ->all(),

                    'list_box_2_title' => $item['list_box_2_title'] ?? '',
                    'list_box_2_count' => max(1, (int)($item['list_box_2_count'] ?? 10)),
                    'list_box_2_category_ids' => collect($item['list_box_2_category_ids'] ?? [])
                        ->filter()
                        ->map(fn($id) => (int)$id)
                        ->values()
                        ->all(),

                    'sort_order' => (int)($item['sort_order'] ?? 0),
                ];
            })
            ->filter(fn(array $item) => filled($item['main_title']))
            ->sortBy('sort_order')
            ->values();
    }
}

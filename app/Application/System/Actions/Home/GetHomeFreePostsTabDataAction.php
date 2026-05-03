<?php

namespace App\Application\System\Actions\Home;

use App\Application\Content\Actions\GetPostFeaturesAction;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Content\Models\Post;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetHomeFreePostsTabDataAction
{
    public function __construct(
        protected GetPostFeaturesAction $getPostFeaturesAction,
    )
    {
    }

    public function execute(array $activeItem): array
    {
        return Cache::tags([
            CacheTag::HOME,
            CacheTag::POSTS,
            CacheTag::HOME_FREE_POSTS,
        ])->remember(
            CacheKey::homeFreePostsTab($this->cachePayload($activeItem)),
            CacheTtl::short(),
            fn() => [
                'mainPosts' => $this->getMainPosts($activeItem),
                'list1Posts' => $this->getList1Posts($activeItem),
                'list2Posts' => $this->getList2Posts($activeItem),
            ]
        );
    }

    protected function getMainPosts(array $activeItem): Collection
    {
        $categoryIds = $this->resolveCategoryIdsWithDescendants(
            $activeItem['main_category_ids'] ?? []
        );

        return Post::query()
            ->with([
                'picture.primaryVariant',
                'picture.variants',
                'featureAssignments.featureDefinition',
                'categories',
            ])
            ->whereHas('categories', function ($query) use ($categoryIds): void {
                $query->whereIn('taxonomy_terms.id', $categoryIds);
            })
            ->latest('published_at')
            ->limit((int)($activeItem['main_count'] ?? 6))
            ->get()
            ->map(function (Post $post) {
                $post->features = $this->getPostFeaturesAction->execute($post);

                return $post;
            });
    }

    protected function getList1Posts(array $activeItem): Collection
    {
        $categoryIds = $this->resolveCategoryIdsWithDescendants(
            $activeItem['list_box_1_category_ids'] ?? []
        );

        return Post::query()
            ->whereHas('categories', function ($query) use ($categoryIds): void {
                $query->whereIn('taxonomy_terms.id', $categoryIds);
            })
            ->latest('published_at')
            ->limit((int)($activeItem['list_box_1_count'] ?? 5))
            ->get();
    }

    protected function getList2Posts(array $activeItem): Collection
    {
        $categoryIds = $this->resolveCategoryIdsWithDescendants(
            $activeItem['list_box_2_category_ids'] ?? []
        );

        return Post::query()
            ->whereHas('categories', function ($query) use ($categoryIds): void {
                $query->whereIn('taxonomy_terms.id', $categoryIds);
            })
            ->latest('published_at')
            ->limit((int)($activeItem['list_box_2_count'] ?? 5))
            ->get();
    }

    /**
     * @param array<int|string> $categoryIds
     * @return array<int>
     */
    protected function resolveCategoryIdsWithDescendants(array $categoryIds): array
    {
        $categoryIds = collect($categoryIds)
            ->filter()
            ->map(fn($id) => (int)$id)
            ->unique()
            ->values();

        if ($categoryIds->isEmpty()) {
            return [];
        }

        return TaxonomyTerm::query()
            ->forTaxonomy(TaxonomyKeyEnum::POST_CATEGORY)
            ->whereIn('id', $categoryIds)
            ->select(['id', 'parent_id', 'taxonomy'])
            ->with('childrenRecursive')
            ->get()
            ->flatMap(fn(TaxonomyTerm $term) => $term->selfAndDescendantIds())
            ->unique()
            ->values()
            ->all();
    }

    protected function cachePayload(array $activeItem): array
    {
        return [
            'key' => $activeItem['key'] ?? null,
            'main_category_ids' => $activeItem['main_category_ids'] ?? [],
            'main_count' => $activeItem['main_count'] ?? null,
            'list_box_1_category_ids' => $activeItem['list_box_1_category_ids'] ?? [],
            'list_box_1_count' => $activeItem['list_box_1_count'] ?? null,
            'list_box_2_category_ids' => $activeItem['list_box_2_category_ids'] ?? [],
            'list_box_2_count' => $activeItem['list_box_2_count'] ?? null,
        ];
    }
}

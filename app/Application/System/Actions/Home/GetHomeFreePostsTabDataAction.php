<?php

namespace App\Application\System\Actions\Home;

use App\Application\Content\Actions\GetPostFeaturesAction;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Content\Models\Post;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Builder;
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
            fn() => $this->getPosts($activeItem)
        );
    }

    protected function getPosts(array $activeItem): array
    {
        $categoryIds = $this->resolveCategoryIdsWithDescendants(
            $activeItem['main_category_ids'] ?? []
        );

        $heroCount = min(12, max(3, (int) ($activeItem['hero_count'] ?? 3)));
        $listCount = min(30, max(0, (int) ($activeItem['list_count'] ?? 8)));
        $query = Post::query()
            ->with([
                'picture.primaryVariant',
                'picture.variants',
                'featureAssignments.featureDefinition',
                'categories',
            ])
            // Livewire update routes bypass ActivePostScope, so always enforce visibility here.
            ->where('status', PostStatusEnum::ACTIVE)
            ->where('published_at', '<=', now())
            ->latest('published_at')
            ->orderByDesc('id');

        $manualHero = $this->resolveManualHero($activeItem, $query);
        $query->whereHas('categories', function ($query) use ($categoryIds): void {
            $query->whereIn('taxonomy_terms.id', $categoryIds);
        });

        if ($manualHero !== null) {
            $query->whereKeyNot($manualHero->getKey());
        }

        $pinned = (clone $query)->where('is_pinned', true)->get();
        $freshHeroCount = $heroCount - ($manualHero !== null ? 1 : 0);
        $fresh = (clone $query)->where(function ($query): void {
            $query->where('is_pinned', false)->orWhereNull('is_pinned');
        })->limit($freshHeroCount + $listCount)->get();
        $heroes = $pinned->concat($fresh->take($freshHeroCount))->values();

        if ($manualHero !== null) {
            $heroes->prepend($manualHero);
        }

        $heroes->each(function (Post $post): void {
            $post->features = $this->getPostFeaturesAction->execute($post);
        });

        return [
            'mainPosts' => $heroes,
            'list1Posts' => $fresh->skip($freshHeroCount)->values(),
            'list2Posts' => collect(),
        ];
    }

    /**
     * کارت اصلیِ دستیِ همان تب (اگر ادمین انتخاب کرده باشد).
     *
     * اولویت: نوشته‌ی مشخص‌شده، بعد تازه‌ترین نوشته‌ی دسته‌ی انتخابی. نوشته باید
     * منتشرشده و فعال باشد؛ وگرنه (مثلاً حذف‌شده) نادیده گرفته می‌شود و رفتار
     * خودکار قبلی برمی‌گردد.
     *
     * @param array<string, mixed> $activeItem
     */
    protected function resolveManualHero(array $activeItem, Builder $baseQuery): ?Post
    {
        $featuredPostId = (int) ($activeItem['featured_post_id'] ?? 0);

        if ($featuredPostId > 0) {
            $post = (clone $baseQuery)->whereKey($featuredPostId)->first();

            if ($post !== null) {
                return $post;
            }
        }

        $featuredCategoryId = (int) ($activeItem['featured_category_id'] ?? 0);

        if ($featuredCategoryId <= 0) {
            return null;
        }

        $categoryIds = $this->resolveCategoryIdsWithDescendants([$featuredCategoryId]);

        if ($categoryIds === []) {
            return null;
        }

        return (clone $baseQuery)
            ->whereHas('categories', function ($query) use ($categoryIds): void {
                $query->whereIn('taxonomy_terms.id', $categoryIds);
            })
            ->latest('published_at')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * @param array<int|string> $categoryIds
     * @return array<int>
     */
    public function resolveCategoryIdsWithDescendants(array $categoryIds): array
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
            'layout' => 'hero-list-v2',
            'key' => $activeItem['key'] ?? null,
            'main_category_ids' => $activeItem['main_category_ids'] ?? [],
            'featured_post_id' => (int) ($activeItem['featured_post_id'] ?? 0),
            'featured_category_id' => (int) ($activeItem['featured_category_id'] ?? 0),
            'hero_count' => min(12, max(3, (int) ($activeItem['hero_count'] ?? 3))),
            'list_count' => min(30, max(0, (int) ($activeItem['list_count'] ?? 8))),
        ];
    }
}

<?php

namespace App\Application\System\Actions\Home;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Content\Models\Post;
use App\Domain\Taxonomy\Enums\TaxonomyKeyEnum;
use App\Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetHomeVideosBlockAction
{
    /**
     * @return array{
     *     title:string,
     *     description:string,
     *     buttonTitle:string,
     *     buttonUrl:string,
     *     posts:Collection<int, Post>
     * }
     */
    public function execute(): array
    {
        return Cache::tags([
            CacheTag::HOME,
            CacheTag::POSTS,
            CacheTag::HOME_VIDEOS,
        ])->remember(
            CacheKey::homeVideosBlock(),
            CacheTtl::short(),
            function () {
                $categoryIds = collect(get_setting('home', 'videos_category_ids') ?? [])
                    ->filter()
                    ->map(fn($id) => (int)$id)
                    ->unique()
                    ->values()
                    ->all();

                $categoryIds = $this->resolveCategoryIdsWithDescendants($categoryIds);

                return [
                    'title' => (string)get_setting('home', 'videos_title', 'آموزش‌های ویدیویی'),
                    'description' => (string)get_setting('home', 'videos_description', ''),
                    'buttonTitle' => (string)get_setting('home', 'videos_button_title', 'مشاهده همه'),
                    'buttonUrl' => (string)get_setting('home', 'videos_button_url', '#'),
                    'posts' => $this->getPosts($categoryIds),
                ];
            }
        );
    }

    /**
     * @param array<int, int> $categoryIds
     * @return Collection<int, Post>
     */
    protected function getPosts(array $categoryIds): Collection
    {
        if (empty($categoryIds)) {
            return collect();
        }

        return Post::query()
            ->with([
                'picture.primaryVariant',
                'picture.variants',
            ])
            ->whereHas('categories', function ($query) use ($categoryIds): void {
                $query->whereIn('taxonomy_terms.id', $categoryIds);
            })
            ->latest('published_at')
            ->limit(4)
            ->get();
    }

    /**
     * @param array<int, int|string> $categoryIds
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
}

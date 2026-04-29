<?php

namespace App\Application\System\Actions\Home;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Content\Models\Post;
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
                    ->unique()
                    ->values()
                    ->all();

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
     * @param array<int, int|string> $categoryIds
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
                'categories',
            ])
            ->whereHas('categories', function ($query) use ($categoryIds) {
                $query->whereIn('taxonomy_terms.id', $categoryIds);
            })
            ->latest('published_at')
            ->limit(4)
            ->get();
    }
}

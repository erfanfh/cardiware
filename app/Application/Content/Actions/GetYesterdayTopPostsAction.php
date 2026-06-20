<?php

namespace App\Application\Content\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Content\Models\Post;
use App\Domain\System\Models\PageViewDetail;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class GetYesterdayTopPostsAction
{
    /**
     * Get yesterday's top viewed posts.
     *
     * @return Collection<int, PageViewDetail>
     */
    public function execute(int $limit = 3): Collection
    {
        $date = now()->subDay()->toDateString();

        return Cache::tags([
            CacheTag::DASHBOARD,
            CacheTag::DASHBOARD_TOP_POSTS,
            CacheTag::PAGE_VIEWS,
            CacheTag::POSTS,
        ])->remember(
            CacheKey::dashboardYesterdayTopPosts($date, $limit),
            CacheTtl::endOfToday(),
            fn() => $this->query($date, $limit)
        );
    }

    /**
     * Query yesterday's top viewed posts.
     *
     * @return Collection<int, PageViewDetail>
     */
    private function query(string $date, int $limit): Collection
    {
        return PageViewDetail::query()
            ->where('pageable_type', Post::class)
            ->whereDate('date', $date)
            ->where('count', '>', 0)
            ->with('pageable')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }
}

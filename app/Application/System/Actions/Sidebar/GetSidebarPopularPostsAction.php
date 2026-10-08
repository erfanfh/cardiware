<?php

namespace App\Application\System\Actions\Sidebar;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Morilog\Jalali\Jalalian;

class GetSidebarPopularPostsAction
{
    /**
     * @return array{title: string, subtitle: string, periods: array<string, Collection<int, Post>>}
     */
    public function execute(): array
    {
        $today = today();
        $date = $today->toDateString();

        return Cache::tags([
            CacheTag::SIDEBAR,
            CacheTag::SIDEBAR_POPULARS,
            CacheTag::POSTS,
            CacheTag::PAGE_VIEWS,
        ])->remember(
            CacheKey::sidebarPopulars($date),
            CacheTtl::short(),
            function () use ($today, $date): array {
                $limit = min(20, max(1, (int) (get_setting('sidebarWidgets', 'populars_posts_count') ?: 5)));
                $weekStart = $today->copy()->startOfWeek(Carbon::SATURDAY)->toDateString();
                $monthStart = Jalalian::fromCarbon($today)->getFirstDayOfMonth()->toCarbon()->toDateString();

                return [
                    'title' => (string) (get_setting('sidebarWidgets', 'populars_title') ?: 'مطالب پربازدید'),
                    'subtitle' => (string) get_setting('sidebarWidgets', 'populars_subtitle'),
                    'periods' => [
                        'day' => $this->popularPosts($date, $date, $limit),
                        'week' => $this->popularPosts($weekStart, $date, $limit),
                        'month' => $this->popularPosts($monthStart, $date, $limit),
                    ],
                ];
            }
        );
    }

    /**
     * @return Collection<int, Post>
     */
    private function popularPosts(string $start, string $end, int $limit): Collection
    {
        $viewTotals = DB::table('page_view_details')
            ->select('pageable_id')
            ->selectRaw('SUM(count) as total_views')
            ->where('pageable_type', Post::class)
            ->whereBetween('date', [$start, $end])
            ->groupBy('pageable_id');

        return Post::query()
            ->select('posts.*', 'view_totals.total_views')
            ->joinSub($viewTotals, 'view_totals', function ($join): void {
                $join->on('view_totals.pageable_id', '=', 'posts.id');
            })
            ->where('posts.status', PostStatusEnum::ACTIVE)
            ->where('posts.published_at', '<=', now())
            ->orderByDesc('view_totals.total_views')
            ->orderByDesc('posts.published_at')
            ->orderByDesc('posts.id')
            ->limit($limit)
            ->with('picture.primaryVariant')
            ->get();
    }
}

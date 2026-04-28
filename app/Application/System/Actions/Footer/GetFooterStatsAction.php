<?php

namespace App\Application\System\Actions\Footer;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Content\Enums\PostStatusEnum;
use App\Domain\Content\Models\Post;
use App\Domain\Discussion\Models\Comment;
use Illuminate\Support\Facades\Cache;

class GetFooterStatsAction
{
    /**
     * @return array{
     *     orders:int,
     *     comments:int,
     *     posts:int,
     *     products:int
     * }
     */
    public function execute(): array
    {
        return Cache::tags([
            CacheTag::FOOTER_STATS,
        ])->remember(
            CacheKey::footerStats(),
            CacheTtl::long(),
            function () {
                $approvedCommentsCount = Comment::approved()->count();

                $activePostsCount = Post::query()
                    ->where('status', PostStatusEnum::ACTIVE)
                    ->count();

                return [
                    'orders' => 12000,
                    'comments' => $this->roundDownToTen($approvedCommentsCount),
                    'posts' => $this->roundDownToTen($activePostsCount),
                    'products' => 30,
                ];
            }
        );
    }

    protected function roundDownToTen(int $value): int
    {
        return (int)(floor($value / 10) * 10);
    }
}

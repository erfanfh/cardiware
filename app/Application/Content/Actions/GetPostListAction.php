<?php

namespace App\Application\Content\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Content\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class GetPostListAction
{
    public function execute(int $page, int $perPage): LengthAwarePaginator
    {
        return Cache::tags([
            CacheTag::POSTS,
            CacheTag::POST_LISTS,
        ])->remember(
            CacheKey::postList($page, $perPage),
            CacheTtl::short(),
            fn() => Post::query()
                ->with([
                    'picture.primaryVariant',
                    'categories',
                ])
                ->latest()
                ->paginate(
                    perPage: $perPage,
                    page: $page,
                )
        );
    }
}

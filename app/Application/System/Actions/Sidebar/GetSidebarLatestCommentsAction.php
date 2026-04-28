<?php

namespace App\Application\System\Actions\Sidebar;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Content\Models\Post;
use App\Domain\Discussion\Models\Comment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class GetSidebarLatestCommentsAction
{
    /**
     * @return array{
     *     title:string,
     *     subtitle:string,
     *     comments:Collection<int, Comment>
     * }
     */
    public function execute(): array
    {
        return Cache::tags([
            CacheTag::SIDEBAR,
            CacheTag::SIDEBAR_COMMENTS,
            CacheTag::COMMENTS,
        ])->remember(
            CacheKey::sidebarLatestComments(),
            CacheTtl::medium(),
            function () {
                $limit = (int)(get_setting('sidebarWidgets', 'comments_count') ?: 5);

                return [
                    'title' => (string)get_setting('sidebarWidgets', 'comments_title'),
                    'subtitle' => (string)get_setting('sidebarWidgets', 'comments_subtitle'),
                    'comments' => Comment::query()
                        ->approved()
                        ->where('commentable_type', Post::class)
                        ->latest('created_at')
                        ->limit($limit)
                        ->with([
                            'commentable',
                            'user',
                        ])
                        ->get(),
                ];
            }
        );
    }
}

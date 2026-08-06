<?php

namespace App\Application\Discussion\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Discussion\Contracts\CommentableContract;
use App\Domain\Discussion\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GetApprovedCommentsCountAction
{
    public function execute(CommentableContract&Model $commentable): int
    {
        return Cache::tags([
            CacheTag::COMMENTS,
            CacheTag::commentable($commentable->getMorphClass(), $commentable->getKey()),
        ])->remember(
            CacheKey::commentableApprovedCommentsCount(
                $commentable->getMorphClass(),
                $commentable->getKey()
            ),
            CacheTtl::medium(),
            fn() => Comment::query()
                ->forCommentable($commentable)
                ->approved()
                ->count()
        );
    }
}

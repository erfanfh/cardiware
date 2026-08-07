<?php

namespace App\Application\Discussion\Actions;

use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Discussion\Contracts\CommentableContract;
use App\Domain\Discussion\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class CalculateCommentableAverageRatingAction
{
    public function execute(CommentableContract&Model $commentable): float
    {
        return Cache::tags([
            CacheTag::COMMENTS,
            CacheTag::commentable($commentable->getMorphClass(), $commentable->getKey()),
        ])->remember(
            CacheKey::commentableAverageRating(
                $commentable->getMorphClass(),
                $commentable->getKey()
            ),
            CacheTtl::medium(),
            function () use ($commentable) {
                $average = Comment::query()
                    ->forCommentable($commentable)
                    ->approved()
                    ->whereNotNull('rating_percent')
                    ->avg('rating_percent');

                return round((float)$average / 20, 2);
            }
        );
    }
}

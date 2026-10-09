<?php

namespace App\Application\Discussion\Actions;

use App\Application\Discussion\DTOs\CommentReviewDTO;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Discussion\Contracts\CommentableContract;
use App\Domain\Discussion\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

final class GetCommentableReviewsAction
{
    /**
     * @return array<int, CommentReviewDTO>
     */
    public function execute(CommentableContract&Model $commentable): array
    {
        return Cache::tags([
            CacheTag::COMMENTS,
            CacheTag::COMMENT_AUTHORS,
            CacheTag::commentable($commentable->getMorphClass(), $commentable->getKey()),
        ])->remember(
            CacheKey::commentableReviews($commentable->getMorphClass(), $commentable->getKey()),
            CacheTtl::medium(),
            function () use ($commentable): array {
                // Match the first public page of product comments before filtering ratings.
                return Comment::query()
                    ->select(['id', 'user_id', 'author_name', 'content', 'rating_percent', 'created_at'])
                    ->forCommentable($commentable)
                    ->approved()
                    ->root()
                    ->pinnedFirst()
                    ->latestFirst()
                    ->with('user:id,display_name,first_name,last_name')
                    ->limit(5)
                    ->get()
                    ->filter(function (Comment $comment): bool {
                        if ($comment->rating_percent < 20 || $comment->rating_percent > 100 || $comment->plain_content === '') {
                            return false;
                        }

                        $author = $comment->user;

                        return $author
                            ? filled($author->display_name) || filled($author->first_name) || filled($author->last_name)
                            : filled($comment->author_name);
                    })
                    ->map(fn (Comment $comment): CommentReviewDTO => new CommentReviewDTO(
                        id: (int) $comment->getKey(),
                        authorName: $comment->display_name,
                        body: $comment->plain_content,
                        rating: round($comment->rating_percent / 20, 2),
                        publishedAt: $comment->created_at?->toAtomString(),
                    ))
                    ->values()
                    ->all();
            },
        );
    }
}

<?php

namespace App\Application\Discussion\Actions;

use App\Application\Discussion\DTOs\CommentRatingSummaryDTO;
use App\Application\Shared\Cache\CacheKey;
use App\Application\Shared\Cache\CacheTag;
use App\Application\Shared\Cache\CacheTtl;
use App\Domain\Discussion\Contracts\CommentableContract;
use App\Domain\Discussion\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

final class GetCommentableRatingSummaryAction
{
    public function execute(CommentableContract&Model $commentable): CommentRatingSummaryDTO
    {
        return Cache::tags([
            CacheTag::COMMENTS,
            CacheTag::commentable($commentable->getMorphClass(), $commentable->getKey()),
        ])->remember(
            CacheKey::commentableRatingSummary(
                $commentable->getMorphClass(),
                $commentable->getKey(),
            ),
            CacheTtl::medium(),
            function () use ($commentable): CommentRatingSummaryDTO {
                $metrics = Comment::query()
                    ->forCommentable($commentable)
                    ->approved()
                    ->root()
                    ->whereBetween('rating_percent', [20, 100])
                    ->selectRaw('COUNT(*) as rating_count, AVG(rating_percent) as average_percent')
                    ->first();

                $ratingCount = (int) ($metrics?->rating_count ?? 0);

                if ($ratingCount === 0 || $metrics?->average_percent === null) {
                    return new CommentRatingSummaryDTO(
                        averageRating: null,
                        ratingCount: 0,
                    );
                }

                return new CommentRatingSummaryDTO(
                    averageRating: round((float) $metrics->average_percent / 20, 2),
                    ratingCount: $ratingCount,
                );
            },
        );
    }
}

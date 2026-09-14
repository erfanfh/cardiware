<?php

namespace App\Application\Discussion\Actions;

use App\Domain\Discussion\Contracts\CommentableContract;
use Illuminate\Database\Eloquent\Model;

class CalculateCommentableAverageRatingAction
{
    public function __construct(
        private readonly GetCommentableRatingSummaryAction $getCommentableRatingSummaryAction,
    )
    {
    }

    public function execute(CommentableContract&Model $commentable): float
    {
        return $this->getCommentableRatingSummaryAction
            ->execute($commentable)
            ->averageRating ?? 0.0;
    }
}

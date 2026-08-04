<?php

namespace App\Application\Discussion\DTOs;

use App\Domain\Discussion\Enums\CommentReactionTypeEnum;

final readonly class ToggleCommentReactionResultDTO
{
    public function __construct(
        public int                      $commentId,
        public int                      $likesCount,
        public int                      $dislikesCount,
        public int                      $score,
        public ?CommentReactionTypeEnum $currentReaction,
    )
    {
    }
}

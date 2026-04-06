<?php

namespace App\Domain\Discussion\Traits;

use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasComments
{
    /**
     * Get all comments of the model.
     *
     * @return MorphMany
     */
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /**
     * All approved comments (including replies).
     *
     * @return MorphMany
     */
    public function approvedComments(): MorphMany
    {
        return $this->comments()->where('status', CommentStatusEnum::APPROVED);
    }
}

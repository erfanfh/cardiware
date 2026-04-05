<?php

namespace App\Domain\Discussion\Traits;

use App\Domain\Discussion\Models\Comment;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait InteractsWithComments
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
}

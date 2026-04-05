<?php

namespace App\Domain\Discussion\Contracts;

interface CommentableContract
{
    /**
     * Determine whether comments are enabled for this model.
     *
     * @return bool
     */
    public function commentsEnabled(): bool;

    /**
     * Determine whether guests can submit comments for this model.
     *
     * @return bool
     */
    public function guestCommentsEnabled(): bool;

    /**
     * Determine whether rating is required when submitting a comment.
     *
     * @return bool
     */
    public function requiresCommentRating(): bool;
}

<?php

namespace App\Application\Discussion\Actions;

use App\Application\Discussion\DTOs\CreateCommentDataDTO;
use App\Domain\Discussion\Contracts\CommentableContract;
use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateCommentAction
{
    /**
     * @throws ValidationException
     */
    public function execute(CreateCommentDataDTO $data): Comment
    {
        $commentable = $data->commentable;

        $this->ensureCommentableIsValid($commentable);
        $this->ensureCommentsAreEnabled($commentable);
        $this->ensureGuestIsAllowedIfNeeded($commentable, $data);
        $this->ensureRatingIsValid($commentable, $data);

        $parent = $this->validateParent($data);

        $status = $this->resolveInitialStatus();

        return DB::transaction(function () use ($data, $commentable, $parent, $status) {
            $comment = new Comment();

            $comment->commentable_type = $commentable->getMorphClass();
            $comment->commentable_id = $commentable->getKey();

            $comment->parent_id = $parent?->id;
            $comment->root_id = $parent?->root_id ?? $parent?->id;

            $comment->user_id = $data->userId;
            $comment->author_name = $data->authorName;
            $comment->author_email = $data->authorEmail;

            $comment->ip_address = $data->ipAddress;
            $comment->user_agent = $data->userAgent;

            $comment->content = trim($data->content);
            $comment->rating_percent = $data->ratingPercent;

            $comment->status = $status;
            $comment->approved_at = $status === CommentStatusEnum::APPROVED ? now() : null;

            $comment->save();

            if ($comment->isRoot()) {
                $comment->root_id = $comment->id;
                $comment->save();
            }

            return $comment;
        });
    }

    protected function ensureCommentableIsValid(Model $model): void
    {
        if (!$model instanceof CommentableContract) {
            throw ValidationException::withMessages([
                'commentable' => 'This model does not support comments.',
            ]);
        }
    }

    protected function ensureCommentsAreEnabled(CommentableContract $commentable): void
    {
        if (!$commentable->commentsEnabled()) {
            throw ValidationException::withMessages([
                'comments' => 'Comments are disabled for this item.',
            ]);
        }
    }

    protected function ensureGuestIsAllowedIfNeeded(CommentableContract $commentable, CreateCommentDataDTO $data): void
    {
        if ($data->userId !== null) {
            return;
        }

        if (!$commentable->guestCommentsEnabled()) {
            throw ValidationException::withMessages([
                'comments' => 'Guests are not allowed to submit comments.',
            ]);
        }

        if (blank($data->authorName)) {
            throw ValidationException::withMessages([
                'author_name' => 'Name is required for guest comments.',
            ]);
        }

        if (blank($data->authorEmail)) {
            throw ValidationException::withMessages([
                'author_email' => 'Email is required for guest comments.',
            ]);
        }
    }

    protected function ensureRatingIsValid(CommentableContract $commentable, CreateCommentDataDTO $data): void
    {
        if ($commentable->requiresCommentRating() && $data->ratingPercent === null) {
            throw ValidationException::withMessages([
                'rating_percent' => 'Rating is required for this item.',
            ]);
        }

        if ($data->ratingPercent !== null && ($data->ratingPercent < 0 || $data->ratingPercent > 100)) {
            throw ValidationException::withMessages([
                'rating_percent' => 'Rating must be between 0 and 100.',
            ]);
        }
    }

    protected function validateParent(CreateCommentDataDTO $data): ?Comment
    {
        if ($data->parentId === null) {
            return null;
        }

        $parent = Comment::query()->find($data->parentId);

        if (!$parent) {
            throw ValidationException::withMessages([
                'parent_id' => 'Parent comment not found.',
            ]);
        }

        if (
            $parent->commentable_type !== $data->commentable->getMorphClass() ||
            (int)$parent->commentable_id !== (int)$data->commentable->getKey()
        ) {
            throw ValidationException::withMessages([
                'parent_id' => 'The parent comment does not belong to this item.',
            ]);
        }

        return $parent;
    }

    protected function resolveInitialStatus(): CommentStatusEnum
    {
        $user = auth()->user();

        if ($user && $user->can('comment.auto_approve')) {
            return CommentStatusEnum::APPROVED;
        }

        return CommentStatusEnum::PENDING;
    }
}

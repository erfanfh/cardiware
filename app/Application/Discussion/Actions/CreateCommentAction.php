<?php

namespace App\Application\Discussion\Actions;

use App\Application\Discussion\DTOs\CreateCommentDataDTO;
use App\Domain\Discussion\Contracts\CommentableContract;
use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Discussion\Models\Comment as CommentModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class CreateCommentAction
{
    /**
     * @throws ValidationException
     * @throws Throwable
     */
    public function execute(CreateCommentDataDTO $data): CommentModel
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

            $comment->user_id = $data->userId;
            $comment->author_name = $data->authorName;
            $comment->author_email = $data->authorEmail;

            $comment->ip_address = $data->ipAddress;
            $comment->user_agent = $data->userAgent;

            $comment->content = $data->content;
            $comment->rating_percent = $data->ratingPercent;

            $comment->status = $status;
            $comment->approved_at = $status === CommentStatusEnum::APPROVED ? now() : null;

            $comment->save();

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
        if ($data->userId === null) {
            if (!$commentable->guestCommentsEnabled()) {
                throw ValidationException::withMessages([
                    'comments' => 'Guests are not allowed to submit comments.',
                ]);
            }

            if (empty($data->authorName) || empty($data->authorEmail)) {
                throw ValidationException::withMessages([
                    'author' => 'Name and email are required for guest comments.',
                ]);
            }
        }
    }

    protected function ensureRatingIsValid(CommentableContract $commentable, CreateCommentDataDTO $data): void
    {
        if ($commentable->requiresCommentRating() && $data->ratingPercent === null) {
            throw ValidationException::withMessages([
                'rating' => 'Rating is required for this item.',
            ]);
        }

        if ($data->ratingPercent !== null) {
            if ($data->ratingPercent < 0 || $data->ratingPercent > 100) {
                throw ValidationException::withMessages([
                    'rating' => 'Rating must be between 0 and 100.',
                ]);
            }
        }
    }

    protected function validateParent(CreateCommentDataDTO $data): ?CommentModel
    {
        if ($data->parentId === null) {
            return null;
        }

        $parent = Comment::query()->find($data->parentId);

        if (!$parent) {
            throw ValidationException::withMessages([
                'parent' => 'Parent comment not found.',
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

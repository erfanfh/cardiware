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
                'commentable' => 'امکان ثبت دیدگاه وجود ندارد.',
            ]);
        }
    }

    protected function ensureCommentsAreEnabled(CommentableContract $commentable): void
    {
        if (!$commentable->commentsEnabled()) {
            throw ValidationException::withMessages([
                'comments' => 'امکان ثبت دیدگاه غیرفعال می باشد.',
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
                'comments' => 'برای ثبت دیدگاه لطفا به حساب کاربری خود وارد شوید.',
            ]);
        }

        if (blank($data->authorName)) {
            throw ValidationException::withMessages([
                'author_name' => 'لطفا نام خود را وارد نمایید.',
            ]);
        }

        if (blank($data->authorEmail)) {
            throw ValidationException::withMessages([
                'author_email' => 'لطفا ایمیل خود را وارد نمایید.',
            ]);
        }
    }

    protected function ensureRatingIsValid(CommentableContract $commentable, CreateCommentDataDTO $data): void
    {
        if ($commentable->requiresCommentRating() && $data->ratingPercent === null) {
            throw ValidationException::withMessages([
                'rating_percent' => 'ثبت نمره الزامی است.',
            ]);
        }

        if ($data->ratingPercent !== null && ($data->ratingPercent < 0 || $data->ratingPercent > 100)) {
            throw ValidationException::withMessages([
                'rating_percent' => 'نمره باید بین 0 تا 100 باشد.',
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
                'parent_id' => 'دیدگاه والد یافت نشد.',
            ]);
        }

        if (
            $parent->commentable_type !== $data->commentable->getMorphClass() ||
            (int)$parent->commentable_id !== (int)$data->commentable->getKey()
        ) {
            throw ValidationException::withMessages([
                'parent_id' => 'دیدگاه والد متعلق به این دیدگاه نیست.',
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

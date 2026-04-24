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
        /** @var CommentableContract $commentable */
        $commentable = $data->commentable;

        $this->ensureCommentableIsValid($commentable);
        $this->ensureCommentsAreEnabled($commentable);
        $this->ensureGuestIsAllowedIfNeeded($commentable, $data);

        $parent = $this->validateParent($data);

        $this->ensureRatingIsValid($commentable, $data, $parent);
        $this->ensureContentIsNotDuplicate($data, $parent);
        if (!$data->bypassCooldown) {
            $this->ensureSubmissionCooldown($data);
        }

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

            $comment->content = $this->normalizeStoredContent($data->content);
            $comment->rating_percent = $parent ? null : $data->ratingPercent;

            $comment->status = $status;
            $comment->approved_at = $status->isApproved() ? now() : null;

            $comment->save();

            if ($comment->isRoot()) {
                $comment->root_id = $comment->id;
                $comment->save();
            }

            return $comment->fresh(['user']);
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

    protected function ensureRatingIsValid(
        CommentableContract  $commentable,
        CreateCommentDataDTO $data,
        ?Comment             $parent
    ): void
    {
        if ($parent !== null && $data->ratingPercent !== null) {
            throw ValidationException::withMessages([
                'rating_percent' => 'ثبت امتیاز فقط برای دیدگاه اصلی مجاز است.',
            ]);
        }

        if ($parent === null && $commentable->requiresCommentRating() && $data->ratingPercent === null) {
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

    protected function ensureContentIsNotDuplicate(CreateCommentDataDTO $data, ?Comment $parent): void
    {
        $normalizedContent = $this->normalizeComparisonContent($data->content);

        $query = Comment::query()
            ->forCommentable($data->commentable)
            ->when(
                $parent,
                fn($builder) => $builder->where('parent_id', $parent->id),
                fn($builder) => $builder->whereNull('parent_id')
            );

        if ($data->userId !== null) {
            $query->where('user_id', $data->userId);
        } else {
            $query->whereNull('user_id')
                ->where('author_email', $data->authorEmail);
        }

        $exists = $query
            ->select(['id', 'content'])
            ->get()
            ->contains(function (Comment $comment) use ($normalizedContent) {
                return $this->normalizeComparisonContent((string)$comment->content) === $normalizedContent;
            });

        if ($exists) {
            throw ValidationException::withMessages([
                'content' => 'شما قبلاً همین دیدگاه را برای این مطلب ثبت کرده‌اید.',
            ]);
        }
    }

    protected function ensureSubmissionCooldown(CreateCommentDataDTO $data): void
    {
        $query = Comment::query()
            ->forCommentable($data->commentable)
            ->where('created_at', '>=', now()->subSeconds(60));

        if ($data->userId !== null) {
            $query->where('user_id', $data->userId);
        } else {
            $query->whereNull('user_id')
                ->where(function ($query) use ($data) {
                    $query->where('author_email', $data->authorEmail)
                        ->orWhere('ip_address', $data->ipAddress);
                });
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'content' => 'در هر دقیقه امکان ثبت بیش از 1 دیدگاه وجود ندارد.',
            ]);
        }
    }

    protected function validateParent(CreateCommentDataDTO $data): ?Comment
    {
        if ($data->parentId === null) {
            return null;
        }

        $parentQuery = Comment::query();

        if (!$data->allowReplyToUnapprovedParent) {
            $parentQuery->approved();
        }

        /** @var Comment|null $parent */
        $parent = $parentQuery->find($data->parentId);

        if (!$parent) {
            throw ValidationException::withMessages([
                'parent_id' => 'دیدگاه والد معتبر نیست.',
            ]);
        }

        if (
            $parent->commentable_type !== $data->commentable->getMorphClass() ||
            (int)$parent->commentable_id !== (int)$data->commentable->getKey()
        ) {
            throw ValidationException::withMessages([
                'parent_id' => 'دیدگاه والد به این محتوا تعلق ندارد.',
            ]);
        }

        return $parent;
    }

    protected function normalizeComparisonContent(string $content): string
    {
        $content = trim($content);
        $content = preg_replace('/\s+/u', ' ', $content) ?? $content;

        return mb_strtolower($content);
    }

    protected function normalizeStoredContent(string $content): string
    {
        return trim($content);
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

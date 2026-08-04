<?php

namespace App\Application\Discussion\Actions;

use App\Application\Discussion\DTOs\CommentReactionIdentityDTO;
use App\Application\Discussion\DTOs\ToggleCommentReactionResultDTO;
use App\Domain\Discussion\Enums\CommentReactionTypeEnum;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Discussion\Models\CommentReaction;
use Illuminate\Support\Facades\DB;

class ToggleCommentReactionAction
{
    public function execute(
        int                        $commentId,
        CommentReactionTypeEnum    $requestedType,
        CommentReactionIdentityDTO $identity,
    ): ToggleCommentReactionResultDTO
    {
        return DB::transaction(function () use (
            $commentId,
            $requestedType,
            $identity
        ): ToggleCommentReactionResultDTO {
            /*
             * تمام واکنش‌های یک دیدگاه از طریق قفل دیدگاه
             * به‌صورت ترتیبی پردازش می‌شوند.
             */
            $comment = Comment::query()
                ->approved()
                ->whereKey($commentId)
                ->lockForUpdate()
                ->firstOrFail();

            $reaction = $this->findCurrentReaction(
                comment: $comment,
                identity: $identity,
            );

            if (!$reaction) {
                /*
                 * کاربر هنوز هیچ واکنشی ثبت نکرده است.
                 */
                $this->createReaction(
                    comment: $comment,
                    identity: $identity,
                    type: $requestedType,
                );

                $this->incrementCounter(
                    comment: $comment,
                    type: $requestedType,
                );

                $currentReaction = $requestedType;
            } elseif ($reaction->type === $requestedType) {
                /*
                 * کلیک دوباره روی همان گزینه باعث حذف واکنش می‌شود.
                 */
                $this->decrementCounter(
                    comment: $comment,
                    type: $reaction->type,
                );

                $reaction->delete();

                $currentReaction = null;
            } else {
                /*
                 * تبدیل مستقیم لایک به دیسلایک یا برعکس.
                 */
                $this->decrementCounter(
                    comment: $comment,
                    type: $reaction->type,
                );

                $this->incrementCounter(
                    comment: $comment,
                    type: $requestedType,
                );

                $reaction->forceFill([
                    'type' => $requestedType,
                ])->save();

                $currentReaction = $requestedType;
            }

            $comment->likes_count = max(
                0,
                (int)$comment->likes_count
            );

            $comment->dislikes_count = max(
                0,
                (int)$comment->dislikes_count
            );

            $comment->score =
                $comment->likes_count - $comment->dislikes_count;

            /*
             * تغییر واکنش نباید Eventهای مدل Comment و Cache دیدگاه‌ها
             * را بی‌دلیل درگیر کند.
             */
            $comment->saveQuietly();

            return new ToggleCommentReactionResultDTO(
                commentId: (int)$comment->getKey(),
                likesCount: (int)$comment->likes_count,
                dislikesCount: (int)$comment->dislikes_count,
                score: (int)$comment->score,
                currentReaction: $currentReaction,
            );
        }, attempts: 3);
    }

    private function findCurrentReaction(
        Comment                    $comment,
        CommentReactionIdentityDTO $identity,
    ): ?CommentReaction
    {
        return CommentReaction::query()
            ->where('comment_id', $comment->getKey())
            ->when(
                $identity->isAuthenticated(),
                fn($query) => $query->where(
                    'user_id',
                    $identity->userId
                ),
                fn($query) => $query
                    ->whereNull('user_id')
                    ->where(
                        'ip_hash',
                        $identity->ipHash
                    )
            )
            ->lockForUpdate()
            ->first();
    }

    private function createReaction(
        Comment                    $comment,
        CommentReactionIdentityDTO $identity,
        CommentReactionTypeEnum    $type,
    ): CommentReaction
    {
        return CommentReaction::query()->create([
            'comment_id' => $comment->getKey(),
            'user_id' => $identity->userId,
            'ip_hash' => $identity->isAuthenticated()
                ? null
                : $identity->ipHash,
            'type' => $type,
        ]);
    }

    private function incrementCounter(
        Comment                 $comment,
        CommentReactionTypeEnum $type,
    ): void
    {
        match ($type) {
            CommentReactionTypeEnum::LIKE =>
            $comment->likes_count++,

            CommentReactionTypeEnum::DISLIKE =>
            $comment->dislikes_count++,
        };
    }

    private function decrementCounter(
        Comment                 $comment,
        CommentReactionTypeEnum $type,
    ): void
    {
        match ($type) {
            CommentReactionTypeEnum::LIKE =>
            $comment->likes_count = max(
                0,
                (int)$comment->likes_count - 1
            ),

            CommentReactionTypeEnum::DISLIKE =>
            $comment->dislikes_count = max(
                0,
                (int)$comment->dislikes_count - 1
            ),
        };
    }
}

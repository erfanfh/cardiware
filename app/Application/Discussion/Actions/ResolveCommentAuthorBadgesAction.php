<?php

namespace App\Application\Discussion\Actions;

use App\Domain\Discussion\Contracts\HasDiscussionOwner;
use App\Domain\Discussion\Contracts\HasVerifiedDiscussionBuyer;
use App\Domain\Discussion\Enums\CommentAuthorBadge;
use App\Domain\Discussion\Models\Comment;
use Illuminate\Database\Eloquent\Model;

class ResolveCommentAuthorBadgesAction
{
    /**
     * نتیجه بررسی خرید در طول همان Request نگهداری می‌شود.
     *
     * @var array<string, bool>
     */
    private array $verifiedBuyerMemo = [];

    /**
     * @return array<int, CommentAuthorBadge>
     */
    public function execute(Comment $comment, Model $commentable): array
    {
        $user = $comment->user;

        /*
         * برای کامنت مهمان هیچ لیبلی نمایش نمی‌دهیم.
         * نام و ایمیل ارسال‌شده توسط مهمان قابل اعتماد نیست.
         */
        if (!$user) {
            return [];
        }

        $badges = [];

        if ($user->isDiscussionAdmin()) {
            $badges[] = CommentAuthorBadge::ADMIN;
        }

        if (
            $commentable instanceof HasDiscussionOwner &&
            $commentable->discussionOwnerUserId() === (int)$user->getKey()
        ) {
            $badges[] = $commentable->discussionOwnerBadge();
        }

        if (
            $commentable instanceof HasVerifiedDiscussionBuyer &&
            $this->hasVerifiedPurchase(
                commentable: $commentable,
                userId: (int)$user->getKey(),
            )
        ) {
            $badges[] = CommentAuthorBadge::VERIFIED_BUYER;
        }

        return collect($badges)
            ->unique(fn(CommentAuthorBadge $badge) => $badge->value)
            ->sortByDesc(fn(CommentAuthorBadge $badge) => $badge->priority())
            ->take(2)
            ->values()
            ->all();
    }

    private function hasVerifiedPurchase(
        HasVerifiedDiscussionBuyer&Model $commentable,
        int                              $userId
    ): bool
    {
        $key = implode(':', [
            $commentable->getMorphClass(),
            $commentable->getKey(),
            $userId,
        ]);

        return $this->verifiedBuyerMemo[$key]
            ??= $commentable->hasVerifiedDiscussionPurchase($userId);
    }
}

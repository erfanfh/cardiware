<?php

namespace App\Application\Discussion\Actions;

use App\Domain\Discussion\Models\Comment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Throwable;

class ToggleCommentPinAction
{
    /**
     * @throws ValidationException|Throwable
     */
    public function execute(
        Comment $comment,
        int     $adminUserId,
    ): Comment
    {
        return DB::transaction(function () use (
            $comment,
            $adminUserId
        ): Comment {
            $comment = Comment::query()
                ->whereKey($comment->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($comment->isReply()) {
                throw ValidationException::withMessages([
                    'comment' => 'پاسخ دیدگاه قابل پین کردن نیست.',
                ]);
            }

            if (!$comment->isApproved()) {
                throw ValidationException::withMessages([
                    'comment' => 'فقط دیدگاه تاییدشده قابل پین کردن است.',
                ]);
            }

            if ($comment->isPinned()) {
                $comment->clearPinAttributes();
            } else {
                $comment->forceFill([
                    'is_pinned' => true,
                    'pinned_at' => now(),
                    'pinned_by' => $adminUserId,
                ]);
            }

            $comment->save();

            return $comment->refresh();
        }, attempts: 3);
    }
}

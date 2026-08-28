<?php

namespace App\Application\Discussion\Actions;

use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Events\CommentApprovedEvent;
use App\Domain\Discussion\Events\CommentDisqualifiedEvent;
use App\Domain\Discussion\Models\Comment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class ChangeCommentStatusAction
{
    public function execute(Comment $comment, CommentStatusEnum $newStatus): Comment
    {
        $oldStatus = $comment->status;

        if ($oldStatus === $newStatus) {
            return $comment->refresh();
        }

        $occurredAt = now(config('app.timezone'))->toImmutable();

        $comment->forceFill([
            'status' => $newStatus,
            'approved_at' => $newStatus->isApproved()
                ? $occurredAt
                : null,
        ])->save();

        $this->dispatchTransitionAfterCommit(
            commentId: (int)$comment->getKey(),
            oldStatus: $oldStatus,
            newStatus: $newStatus,
            occurredAt: $occurredAt,
        );

        return $comment->refresh();
    }

    private function dispatchTransitionAfterCommit(
        int $commentId,
        CommentStatusEnum $oldStatus,
        CommentStatusEnum $newStatus,
        CarbonImmutable $occurredAt,
    ): void {
        if (!$oldStatus->isApproved() && $newStatus->isApproved()) {
            DB::afterCommit(
                fn() => event(
                    new CommentApprovedEvent(
                        commentId: $commentId,
                        occurredAt: $occurredAt,
                    )
                )
            );

            return;
        }

        if ($oldStatus->isApproved() && !$newStatus->isApproved()) {
            DB::afterCommit(
                fn() => event(
                    new CommentDisqualifiedEvent(
                        commentId: $commentId,
                        reason: $newStatus->value,
                        occurredAt: $occurredAt,
                    )
                )
            );
        }
    }
}

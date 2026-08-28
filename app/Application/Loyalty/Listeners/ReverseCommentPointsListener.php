<?php

namespace App\Application\Loyalty\Listeners;

use App\Application\Loyalty\Actions\ReverseEarnedPointsForReferenceAction;
use App\Domain\Discussion\Events\CommentDisqualifiedEvent;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use Throwable;

class ReverseCommentPointsListener
{
    public function __construct(
        private readonly ReverseEarnedPointsForReferenceAction $reverseEarnedPointsForReferenceAction,
    ) {
    }

    public function handle(CommentDisqualifiedEvent $event): void
    {
        try {
            $comment = Comment::query()
                ->withTrashed()
                ->with('user')
                ->find($event->commentId);

            if (!$comment?->user) {
                return;
            }

            $occurrence = $event->occurredAt->utc()->format('YmdHis.u');

            $this->reverseEarnedPointsForReferenceAction->execute(
                user: $comment->user,
                reference: $comment,
                reason: EarningRuleTypeEnum::COMMENT->value,
                eventKey: "loyalty:reverse:comment:{$comment->getKey()}:{$occurrence}",
                occurredAt: $event->occurredAt,
                metadata: [
                    'event_type' => 'comment_disqualified',
                    'disqualification_reason' => $event->reason,
                    'commentable_type' => $comment->commentable_type,
                    'commentable_id' => $comment->commentable_id,
                ],
                description: 'برگشت امتیاز دیدگاه',
            );
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}

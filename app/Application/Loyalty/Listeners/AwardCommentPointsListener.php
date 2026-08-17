<?php

namespace App\Application\Loyalty\Listeners;

use App\Application\Loyalty\Earning\Actions\ProcessEarningRuleAction;
use App\Application\Loyalty\Earning\Contexts\CommentEarningContext;
use App\Domain\Discussion\Events\CommentApprovedEvent;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Loyalty\Enums\EarningRuleTypeEnum;
use Throwable;

class AwardCommentPointsListener
{
    public function __construct(
        private readonly ProcessEarningRuleAction $processEarningRuleAction,
    ) {
    }

    public function handle(CommentApprovedEvent $event): void
    {
        try {
            $comment = Comment::query()->with('user')->find($event->commentId);
            if (!$comment?->user) {
                return;
            }

            $this->processEarningRuleAction->execute(
                EarningRuleTypeEnum::COMMENT,
                new CommentEarningContext(
                    user: $comment->user,
                    comment: $comment,
                    occurredAt: $event->occurredAt,
                ),
            );
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}

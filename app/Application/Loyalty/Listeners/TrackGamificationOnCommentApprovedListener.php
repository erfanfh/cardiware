<?php

namespace App\Application\Loyalty\Listeners;

use App\Application\Loyalty\Achievements\Actions\EvaluateUserAchievementsAction;
use App\Application\Loyalty\Missions\Actions\RecordMissionProgressAction;
use App\Domain\Discussion\Events\CommentApprovedEvent;
use App\Domain\Discussion\Models\Comment;
use App\Domain\Loyalty\Enums\MissionMetricEnum;
use Throwable;

/**
 * پیشرفت مأموریت‌ها و ارزیابی نشان‌ها پس از تأیید دیدگاه.
 * هیچ‌وقت نباید جریان تأیید دیدگاه را شکست دهد.
 */
class TrackGamificationOnCommentApprovedListener
{
    public function __construct(
        private readonly RecordMissionProgressAction $recordMissionProgress,
        private readonly EvaluateUserAchievementsAction $evaluateAchievements,
    ) {
    }

    public function handle(CommentApprovedEvent $event): void
    {
        try {
            $comment = Comment::query()->with('user')->find($event->commentId);

            if (! $comment?->user) {
                return;
            }

            $this->recordMissionProgress->execute(
                $comment->user,
                MissionMetricEnum::APPROVED_COMMENTS,
            );

            $this->evaluateAchievements->execute($comment->user);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}

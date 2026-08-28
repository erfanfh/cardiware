<?php

namespace App\Application\Discussion\Actions;

use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Events\CommentDisqualifiedEvent;
use App\Domain\Discussion\Models\Comment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class HandleCommentBulkAction
{
    public function __construct(
        private readonly ChangeCommentStatusAction $changeCommentStatusAction,
    ) {
    }

    /**
     * @param string $action
     * @param array<int> $ids
     * @return array{action: string, affected: int}
     * @throws Throwable
     */
    public function execute(string $action, array $ids): array
    {
        return DB::transaction(function () use ($action, $ids) {
            $affected = match ($action) {
                'approve' => $this->changeStatus($ids, CommentStatusEnum::APPROVED),
                'reject' => $this->changeStatus($ids, CommentStatusEnum::REJECTED),
                'pending' => $this->changeStatus($ids, CommentStatusEnum::PENDING),
                'delete' => $this->delete($ids),
                default => throw new InvalidArgumentException('Bulk action is not supported.'),
            };

            return [
                'action' => $action,
                'affected' => $affected,
            ];
        });
    }

    /**
     * @param array{action: string, affected: int} $result
     */
    public function successMessage(array $result): string
    {
        $count = $result['affected'];

        return match ($result['action']) {
            'approve' => "{$count} دیدگاه با موفقیت تایید شد.",
            'pending' => "{$count} دیدگاه با موفقیت به در حال انتظار منتقل شد.",
            'reject' => "{$count} دیدگاه با موفقیت رد شد.",
            'delete' => "{$count} دیدگاه با موفقیت حذف شد.",
            default => 'عملیات با موفقیت انجام شد.',
        };
    }

    /**
     * @param array<int> $ids
     */
    private function changeStatus(array $ids, CommentStatusEnum $status): int
    {
        $comments = Comment::query()
            ->whereIn('id', $ids)
            ->get();

        $affected = 0;

        foreach ($comments as $comment) {
            if ($comment->status === $status) {
                continue;
            }

            $this->changeCommentStatusAction->execute($comment, $status);
            $affected++;
        }

        return $affected;
    }

    /**
     * @param array<int> $ids
     */
    private function delete(array $ids): int
    {
        $comments = Comment::query()
            ->whereIn('id', $ids)
            ->get();

        $affected = 0;

        foreach ($comments as $comment) {
            $wasApproved = $comment->isApproved();
            $commentId = (int)$comment->getKey();
            $occurredAt = now(config('app.timezone'))->toImmutable();

            $comment->delete();
            $affected++;

            if (!$wasApproved) {
                continue;
            }

            DB::afterCommit(
                fn() => event(
                    new CommentDisqualifiedEvent(
                        commentId: $commentId,
                        reason: 'deleted',
                        occurredAt: $occurredAt,
                    )
                )
            );
        }

        return $affected;
    }
}

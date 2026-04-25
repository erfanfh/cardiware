<?php

namespace App\Application\Discussion\Actions;

use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Models\Comment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Throwable;

class HandleCommentBulkAction
{
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
                'approve' => $this->approve($ids),
                'reject' => $this->reject($ids),
                'pending' => $this->pending($ids),
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
    protected function approve(array $ids): int
    {
        return Comment::query()
            ->whereIn('id', $ids)
            ->update([
                'status' => CommentStatusEnum::APPROVED,
                'approved_at' => now(),
            ]);
    }

    /**
     * @param array<int> $ids
     */
    protected function pending(array $ids): int
    {
        return Comment::query()
            ->whereIn('id', $ids)
            ->update([
                'status' => CommentStatusEnum::PENDING,
                'approved_at' => null,
            ]);
    }

    /**
     * @param array<int> $ids
     */
    protected function reject(array $ids): int
    {
        return Comment::query()
            ->whereIn('id', $ids)
            ->update([
                'status' => CommentStatusEnum::REJECTED,
                'approved_at' => null,
            ]);
    }

    /**
     * @param array<int> $ids
     */
    protected function delete(array $ids): int
    {
        return Comment::query()
            ->whereIn('id', $ids)
            ->get()
            ->filter(fn(Comment $comment) => !$comment->trashed())
            ->tap(fn($comments) => $comments->each->delete())
            ->count();
    }
}

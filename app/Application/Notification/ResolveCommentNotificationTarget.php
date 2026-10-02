<?php

namespace App\Application\Notification;

use App\Application\Discussion\Support\PendingCommentSessionStore;
use App\Domain\Discussion\Models\Comment;
use Illuminate\Database\Eloquent\Model;

class ResolveCommentNotificationTarget
{
    /** @return array<int, int> Root-to-target path, or an empty array when the viewer cannot see it. */
    public function execute(Model $commentable, mixed $rawTargetId): array
    {
        $targetId = filter_var($rawTargetId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($targetId === false || $targetId === null) {
            return [];
        }

        $guestPendingIds = app(PendingCommentSessionStore::class)->all();
        $target = Comment::query()
            ->forCommentable($commentable)
            ->visibleForFrontend($guestPendingIds)
            ->find($targetId);

        if (!$target) {
            return [];
        }

        $path = [];
        $current = $target;
        for ($depth = 0; $current && $depth < 30; $depth++) {
            array_unshift($path, (int) $current->id);
            if ($current->parent_id === null) {
                break;
            }
            $current = Comment::query()
                ->forCommentable($commentable)
                ->visibleForFrontend($guestPendingIds)
                ->find($current->parent_id);
        }

        return $current && $current->parent_id === null ? $path : [];
    }
}

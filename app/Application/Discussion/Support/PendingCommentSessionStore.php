<?php

namespace App\Application\Discussion\Support;

class PendingCommentSessionStore
{
    private const SESSION_KEY = 'discussion.pending_comment_ids';

    public function all(): array
    {
        return collect(session(self::SESSION_KEY, []))
            ->map(fn($id) => (int)$id)
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    public function add(int $commentId): void
    {
        $ids = collect($this->all())
            ->push($commentId)
            ->unique()
            ->values()
            ->all();

        session([self::SESSION_KEY => $ids]);
    }

    public function has(int $commentId): bool
    {
        return in_array($commentId, $this->all(), true);
    }
}

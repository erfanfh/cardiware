<?php

namespace App\Application\Discussion\Actions;

use App\Domain\Discussion\Models\Comment;

class BuildCommentTreeAction
{
    /**
     * @param array<int, Comment> $rootComments
     * @param array<int, Comment> $replies
     * @return array<int, array{comment: Comment, replies: array}>
     */
    public function execute(array $rootComments, array $replies): array
    {
        $nodesById = [];

        foreach ($rootComments as $comment) {
            $nodesById[$comment->id] = [
                'comment' => $comment,
                'replies' => [],
            ];
        }

        foreach ($replies as $comment) {
            $nodesById[$comment->id] = [
                'comment' => $comment,
                'replies' => [],
            ];
        }

        foreach ($replies as $comment) {
            if ($comment->parent_id === null) {
                continue;
            }

            if (!isset($nodesById[$comment->parent_id])) {
                continue;
            }

            $nodesById[$comment->parent_id]['replies'][] = &$nodesById[$comment->id];
        }

        $tree = [];

        foreach ($rootComments as $comment) {
            $tree[] = $nodesById[$comment->id];
        }

        return $tree;
    }
}

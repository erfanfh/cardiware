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

        foreach ($rootComments as $rootComment) {
            $nodesById[$rootComment->id] = [
                'comment' => $rootComment,
                'replies' => [],
            ];
        }

        foreach ($replies as $reply) {
            $nodesById[$reply->id] = [
                'comment' => $reply,
                'replies' => [],
            ];
        }

        $tree = [];

        foreach ($rootComments as $rootComment) {
            $tree[] = &$nodesById[$rootComment->id];
        }

        foreach ($replies as $reply) {
            if ($reply->parent_id === null) {
                continue;
            }

            if (!isset($nodesById[$reply->parent_id])) {
                continue;
            }

            $nodesById[$reply->parent_id]['replies'][] = &$nodesById[$reply->id];
        }

        return $tree;
    }
}

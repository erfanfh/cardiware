<?php

namespace App\Application\Discussion\Actions;

use App\Application\Discussion\DTOs\GetCommentsForCommentableDataDTO;
use App\Domain\Discussion\Contracts\CommentableContract;
use App\Domain\Discussion\Enums\CommentSortEnum;
use App\Domain\Discussion\Models\Comment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

class GetCommentsForCommentableAction
{
    public function __construct(
        protected BuildCommentTreeAction $buildCommentTreeAction,
    )
    {
    }

    /**
     * @throws ValidationException
     */
    public function execute(GetCommentsForCommentableDataDTO $data): LengthAwarePaginator
    {
        if (!$data->commentable instanceof CommentableContract) {
            throw ValidationException::withMessages([
                'commentable' => 'This model does not support comments.',
            ]);
        }

        $rootCommentsQuery = Comment::query()
            ->forCommentable($data->commentable)
            ->root()
            ->with(['user']);

        if ($data->onlyApproved) {
            $rootCommentsQuery->approved();
        }

        $this->applyRootSort($rootCommentsQuery, $data->sort);

        $rootComments = $rootCommentsQuery->paginate($data->perPage);

        $rootIds = $rootComments->getCollection()->pluck('id')->all();

        if (empty($rootIds)) {
            return $rootComments->through(fn(Comment $comment) => [
                'comment' => $comment,
                'replies' => [],
            ]);
        }

        $threadCommentsQuery = Comment::query()
            ->forCommentable($data->commentable)
            ->forRootIds($rootIds)
            ->repliesOnly()
            ->with(['user'])
            ->oldestFirst();

        if ($data->onlyApproved) {
            $threadCommentsQuery->approved();
        }

        $threadComments = $threadCommentsQuery->get();

        $tree = $this->buildCommentTreeAction->execute(
            rootComments: $rootComments->getCollection()->all(),
            replies: $threadComments->all(),
        );

        $rootComments->setCollection(collect($tree));

        return $rootComments;
    }

    protected function applyRootSort(Builder $query, CommentSortEnum $sort): void
    {
        match ($sort) {
            CommentSortEnum::NEWEST => $query->latestFirst(),
            CommentSortEnum::OLDEST => $query->oldestFirst(),
            CommentSortEnum::BEST => $query
                ->orderByDesc('score')
                ->orderByDesc('likes_count')
                ->orderByDesc('created_at')
                ->orderByDesc('id'),
        };
    }
}

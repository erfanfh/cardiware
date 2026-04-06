<?php

namespace App\Http\Controllers\Front\Discussion;

use App\Domain\Content\Models\Post;
use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Models\Comment;
use App\Http\Controllers\Controller;
use App\Http\Resources\Front\Discussion\FrontCommentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ListPostCommentsController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param Request $request
     * @param Post $post
     * @return JsonResponse
     */
    public function __invoke(Request $request, Post $post): JsonResponse
    {
        $sort = $this->resolveSort($request->string('sort')->toString());

        $query = Comment::query()
            ->where('commentable_type', $post->getMorphClass())
            ->where('commentable_id', $post->getKey())
            ->where('status', CommentStatusEnum::APPROVED)
            ->whereNull('parent_id')
            ->with([
                'approvedReplies' => fn($query) => $query->oldest('created_at'),
            ]);

        $this->applySort($query, $sort);

        $comments = $query->paginate(10)->withQueryString();

        return response()->json([
            'data' => FrontCommentResource::collection($comments->items()),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
            ],
            'stats' => [
                'total_comments' => $comments->total(),
                'average_rating' => $this->resolveAverageRating($post),
            ],
        ]);
    }

    /**
     * @param string $sort
     * @return string
     */
    protected function resolveSort(string $sort): string
    {
        return in_array($sort, ['new', 'top', 'high'], true)
            ? $sort
            : 'new';
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $sort
     * @return void
     */
    protected function applySort($query, string $sort): void
    {
        match ($sort) {
            'top' => $query->orderByDesc('likes_count')->orderByDesc('created_at'),
            'high' => $query->orderByDesc('rating_percent')->orderByDesc('created_at'),
            default => $query->orderByDesc('created_at'),
        };
    }

    /**
     * @param Post $post
     * @return float|null
     */
    protected function resolveAverageRating(Post $post): ?float
    {
        $averagePercent = Comment::query()
            ->where('commentable_type', $post->getMorphClass())
            ->where('commentable_id', $post->getKey())
            ->where('status', CommentStatusEnum::APPROVED)
            ->whereNull('parent_id')
            ->whereNotNull('rating_percent')
            ->avg('rating_percent');

        if ($averagePercent === null) {
            return null;
        }

        return round($averagePercent / 20, 1);
    }
}

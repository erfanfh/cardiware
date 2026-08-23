<?php

namespace App\Http\Controllers\Admin\Discussion;

use App\Application\Discussion\Actions\UpdateCommentAction;
use App\Domain\Discussion\Models\Comment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Discussion\UpdateCommentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class UpdateCommentController extends Controller
{
    public function __invoke(
        UpdateCommentRequest $request,
        Comment $comment,
        UpdateCommentAction $updateCommentAction,
    ): JsonResponse {
        Gate::authorize('comment.edit');

        $comment = $updateCommentAction->execute(
            comment: $comment,
            content: $request->validated('content'),
            ratingStars: $request->validated('rating_stars'),
        );

        return response()->json([
            'ok' => true,
            'message' => 'دیدگاه با موفقیت ویرایش شد.',
            'comment' => [
                'id' => (int)$comment->getKey(),
                'content' => $comment->content,
                'rating_percent' => $comment->rating_percent,
                'rating_stars' => $comment->rating_stars,
            ],
        ]);
    }
}

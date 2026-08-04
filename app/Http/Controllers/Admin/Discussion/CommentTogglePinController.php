<?php

namespace App\Http\Controllers\Admin\Discussion;

use App\Application\Discussion\Actions\ToggleCommentPinAction;
use App\Domain\Discussion\Models\Comment;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CommentTogglePinController extends Controller
{
    public function __invoke(
        Comment                $comment,
        ToggleCommentPinAction $toggleCommentPinAction,
    ): JsonResponse
    {
        Gate::authorize('comment.edit');

        try {
            $comment = $toggleCommentPinAction->execute(
                comment: $comment,
                adminUserId: (int)Auth::id(),
            );

            return response()->json([
                'ok' => true,

                'message' => $comment->isPinned()
                    ? 'دیدگاه با موفقیت پین شد.'
                    : 'دیدگاه از حالت پین خارج شد.',

                'comment' => [
                    'id' => (int)$comment->getKey(),
                    'is_pinned' => $comment->isPinned(),
                    'pinned_at' => $comment->pinned_at?->toISOString(),
                ],
            ]);
        } catch (ValidationException $exception) {
            $message = collect($exception->errors())
                ->flatten()
                ->first();

            return response()->json([
                'ok' => false,
                'message' => $message ?: 'امکان تغییر وضعیت پین وجود ندارد.',
                'errors' => $exception->errors(),
            ], 422);
        }
    }
}

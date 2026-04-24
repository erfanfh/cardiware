<?php

namespace App\Http\Controllers\Admin\Discussion;

use App\Application\Discussion\Actions\CreateCommentAction;
use App\Application\Discussion\DTOs\CreateCommentDataDTO;
use App\Domain\Discussion\Models\Comment;
use App\Http\Requests\Admin\Discussion\ReplyToCommentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CommentReplyController
{
    public function __invoke(
        ReplyToCommentRequest $request,
        Comment               $comment,
        CreateCommentAction   $createCommentAction,
    ): JsonResponse
    {
        Gate::authorize('comment.edit');

        if ($comment->trashed()) {
            throw ValidationException::withMessages([
                'comment' => 'امکان پاسخ به دیدگاه حذف‌شده وجود ندارد.',
            ]);
        }

        $reply = $createCommentAction->execute(new CreateCommentDataDTO(
            commentable: $comment->commentable,
            parentId: $comment->id,
            userId: auth()->id(),
            authorName: null,
            authorEmail: null,
            content: $request->content(),
            ratingPercent: null,
            ipAddress: $request->ip(),
            userAgent: $request->userAgent(),
            allowReplyToUnapprovedParent: true,
            bypassCooldown: true,
        ));

        return response()->json([
            'ok' => true,
            'message' => 'پاسخ با موفقیت ثبت شد.',
            'reply' => [
                'id' => $reply->id,
                'parent_id' => $reply->parent_id,
                'display_name' => $reply->display_name,
                'content' => $reply->content,
                'status' => $reply->status?->value,
                'created_at_date' => jalali_date($reply->created_at, 'Y/m/d'),
                'created_at_time' => jalali_date($reply->created_at, 'H:i'),
            ],
        ]);
    }
}

<?php

namespace App\Http\Controllers\Admin\Discussion;

use App\Application\Discussion\Actions\ChangeCommentStatusAction;
use App\Application\Discussion\Actions\CreateCommentAction;
use App\Application\Discussion\DTOs\CreateCommentDataDTO;
use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Models\Comment;
use App\Http\Requests\Admin\Discussion\ReplyToCommentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Throwable;

class CommentReplyController
{
    /**
     * @throws Throwable
     */
    public function __invoke(
        ReplyToCommentRequest $request,
        Comment                   $comment,
        CreateCommentAction       $createCommentAction,
        ChangeCommentStatusAction $changeCommentStatusAction,
    ): JsonResponse
    {
        Gate::authorize('comment.edit');

        if ($comment->trashed()) {
            throw ValidationException::withMessages([
                'comment' => 'امکان پاسخ به دیدگاه حذف‌شده وجود ندارد.',
            ]);
        }

        $reply = DB::transaction(function () use (
            $comment,
            $request,
            $createCommentAction,
            $changeCommentStatusAction,
        ) {
            if (
                auth()->user()->can('comment.auto_approve')
                && !$comment->isApproved()
            ) {
                $comment = $changeCommentStatusAction->execute(
                    $comment,
                    CommentStatusEnum::APPROVED,
                );
            }

            return $createCommentAction->execute(new CreateCommentDataDTO(
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
        });

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

            'parent_comment' => $reply->parent ? [
                'id' => $reply->parent->id,
                'status' => $reply->parent->status?->value,
            ] : null,
        ]);
    }
}

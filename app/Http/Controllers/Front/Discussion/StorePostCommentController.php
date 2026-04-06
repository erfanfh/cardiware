<?php

namespace App\Http\Controllers\Front\Discussion;

use App\Application\Discussion\Actions\CreateCommentAction;
use App\Application\Discussion\DTOs\CreateCommentDataDTO;
use App\Domain\Content\Models\Post;
use App\Domain\Discussion\Enums\CommentStatusEnum;
use App\Domain\Discussion\Models\Comment;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\Discussion\StorePostCommentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class StorePostCommentController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function __invoke(
        StorePostCommentRequest $request,
        Post                    $post,
        CreateCommentAction     $createCommentAction,
    ): RedirectResponse|JsonResponse
    {
        $user = $request->user();

        $comment = $createCommentAction->execute(
            new CreateCommentDataDTO(
                commentable: $post,
                parentId: $request->integer('parent_id') ?: null,
                userId: $user?->id,
                authorName: $user ? null : $request->input('author_name'),
                authorEmail: $user ? null : $request->input('author_email'),
                content: (string)$request->input('content'),
                ratingPercent: $request->input('rating_percent') !== null
                    ? (int)$request->input('rating_percent')
                    : null,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            )
        );

        $message = $comment->status === CommentStatusEnum::APPROVED
            ? 'دیدگاه شما با موفقیت ثبت و منتشر شد.'
            : 'دیدگاه شما با موفقیت ثبت شد و پس از تأیید نمایش داده می‌شود.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'status' => $comment->status->value,
                'comment' => $comment->status === CommentStatusEnum::APPROVED
                    ? $this->transformCommentForFront($comment)
                    : null,
            ]);
        }

        return back()->with('success', $message);
    }

    protected function transformCommentForFront(Comment $comment): array
    {
        return [
            'id' => $comment->id,
            'name' => $comment->display_name,
            'time' => 'همین الان',
            'text' => $comment->content,
            'rating' => $comment->rating_percent !== null
                ? (int)round($comment->rating_percent / 20)
                : 0,
            'likes' => 0,
            'liked' => false,
            'replies' => [],
        ];
    }
}

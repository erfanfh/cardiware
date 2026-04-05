<?php

namespace App\Http\Controllers\Front\Discussion;

use App\Application\Discussion\Actions\CreateCommentAction;
use App\Application\Discussion\DTOs\CreateCommentDataDTO;
use App\Domain\Content\Models\Post;
use App\Http\Controllers\Controller;
use App\Http\Requests\Front\Discussion\StorePostCommentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;

class StorePostCommentController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param StorePostCommentRequest $request
     * @param Post $post
     * @param CreateCommentAction $createCommentAction
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function __invoke(
        StorePostCommentRequest $request,
        Post                    $post,
        CreateCommentAction     $createCommentAction,
    ): RedirectResponse
    {
        $user = $request->user();

        $createCommentAction->execute(
            new CreateCommentDataDTO(
                commentable: $post,
                parentId: $request->integer('parent_id') ?: null,
                userId: $user?->id,
                authorName: $user ? null : $request->input('author_name'),
                authorEmail: $user ? null : $request->input('author_email'),
                content: $request->string('content')->toString(),
                ratingPercent: $request->input('rating_percent') !== null
                    ? (int)$request->input('rating_percent')
                    : null,
                ipAddress: $request->ip(),
                userAgent: $request->userAgent(),
            )
        );

        return back()->with('success', 'دیدگاه شما با موفقیت ثبت شد و پس از تأیید نمایش داده می‌شود.');
    }
}

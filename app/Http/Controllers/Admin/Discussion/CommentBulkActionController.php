<?php

namespace App\Http\Controllers\Admin\Discussion;

use App\Application\Discussion\Actions\HandleCommentBulkAction;
use App\Http\Requests\Admin\Discussion\BulkCommentActionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Throwable;

class CommentBulkActionController
{
    /**
     * @throws Throwable
     */
    public function __invoke(
        BulkCommentActionRequest $request,
        HandleCommentBulkAction  $actionHandler
    ): RedirectResponse
    {
        $this->authorizeBulkAction($request->bulkAction());

        $result = $actionHandler->execute(
            action: $request->bulkAction(),
            ids: $request->commentIds(),
        );

        return redirect()
            ->route('admin.comments.index')
            ->with('success', $actionHandler->successMessage($result));
    }

    protected function authorizeBulkAction(string $action): void
    {
        match ($action) {
            'approve' => Gate::authorize('comment.approve'),
            'reject' => Gate::authorize('comment.reject'),
            'delete' => Gate::authorize('comment.delete'),
            default => Gate::authorize('comment.list'),
        };
    }
}

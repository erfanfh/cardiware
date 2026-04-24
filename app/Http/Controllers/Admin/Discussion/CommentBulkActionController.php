<?php

namespace App\Http\Controllers\Admin\Discussion;

use App\Application\Discussion\Actions\HandleCommentBulkAction;
use App\Http\Requests\Admin\Discussion\BulkCommentActionRequest;
use Illuminate\Http\JsonResponse;
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
    ): JsonResponse|RedirectResponse
    {
        $this->authorizeBulkAction($request->bulkAction());

        $result = $actionHandler->execute(
            action: $request->bulkAction(),
            ids: $request->commentIds(),
        );

        $message = $actionHandler->successMessage($result);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => $message,
                'action' => $request->bulkAction(),
                'ids' => $request->commentIds(),
                'result' => $result,
            ]);
        }

        return redirect()
            ->route('admin.comments.index')
            ->with('success', $message);
    }

    protected function authorizeBulkAction(string $action): void
    {
        match ($action) {
            'delete' => Gate::authorize('comment.delete'),
            default => Gate::authorize('comment.edit'),
        };
    }
}

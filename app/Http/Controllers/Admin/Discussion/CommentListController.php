<?php

namespace App\Http\Controllers\Admin\Discussion;

use App\Domain\Discussion\Models\Comment;
use App\Http\Controllers\Controller;
use App\Infrastructure\Support\BulkActions\CommentBulkActions;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CommentListController extends Controller
{
    public function __invoke(): View
    {
        Gate::authorize('comment.list');

        $comments = Comment::query()
            ->with(['user', 'commentable'])
            ->latest('created_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $bulkActionOptions = CommentBulkActions::set()->toOptions();

        return view('admin.discussion.comments.index', compact('comments', 'bulkActionOptions'));
    }
}

<?php

namespace App\Http\Controllers\Admin\Discussion;

use App\Domain\Discussion\Models\Comment;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function index(): View
    {
        Gate::authorize('comment.list');

        $comments = Comment::query()
            ->with(['user', 'commentable'])
            ->latest('created_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.discussion.comments.index', compact('comments'));
    }
}

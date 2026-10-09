<?php

namespace App\Http\Controllers\Admin\Content;

use App\Application\Content\Support\PostEditLock;
use App\Domain\Content\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class PostEditLockController
{
    public function renew(Request $request, Post $post, PostEditLock $locks): Response
    {
        Gate::authorize('post.edit');
        $data = $request->validate(['edit_lock_token' => ['required', 'string', 'size:64']]);
        $locks->renew($post, $request->user(), $data['edit_lock_token']);

        return response()->noContent();
    }

    public function release(Request $request, Post $post, PostEditLock $locks): Response
    {
        Gate::authorize('post.edit');
        $data = $request->validate(['edit_lock_token' => ['required', 'string', 'size:64']]);
        $locks->release($post, $request->user(), $data['edit_lock_token']);

        return response()->noContent();
    }
}

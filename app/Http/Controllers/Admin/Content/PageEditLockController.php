<?php

namespace App\Http\Controllers\Admin\Content;

use App\Application\Content\Support\PageEditLock;
use App\Domain\Content\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class PageEditLockController
{
    public function renew(Request $request, Page $page, PageEditLock $locks): Response
    {
        Gate::authorize('page.edit');
        $data = $request->validate(['edit_lock_token' => ['required', 'string', 'size:64']]);
        $locks->renew($page, $request->user(), $data['edit_lock_token']);

        return response()->noContent();
    }

    public function release(Request $request, Page $page, PageEditLock $locks): Response
    {
        Gate::authorize('page.edit');
        $data = $request->validate(['edit_lock_token' => ['required', 'string', 'size:64']]);
        $locks->release($page, $request->user(), $data['edit_lock_token']);

        return response()->noContent();
    }
}

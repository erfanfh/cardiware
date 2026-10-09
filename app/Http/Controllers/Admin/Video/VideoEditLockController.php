<?php

namespace App\Http\Controllers\Admin\Video;

use App\Application\Video\Support\VideoEditLock;
use App\Domain\Video\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class VideoEditLockController
{
    public function renew(Request $request, Video $video, VideoEditLock $locks): Response
    {
        Gate::authorize('video.edit');
        $data = $request->validate(['edit_lock_token' => ['required', 'string', 'size:64']]);
        $locks->renew($video, $request->user(), $data['edit_lock_token']);

        return response()->noContent();
    }

    public function release(Request $request, Video $video, VideoEditLock $locks): Response
    {
        Gate::authorize('video.edit');
        $data = $request->validate(['edit_lock_token' => ['required', 'string', 'size:64']]);
        $locks->release($video, $request->user(), $data['edit_lock_token']);

        return response()->noContent();
    }
}

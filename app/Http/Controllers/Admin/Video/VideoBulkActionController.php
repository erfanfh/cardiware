<?php

namespace App\Http\Controllers\Admin\Video;

use App\Application\Video\Actions\HandleVideoBulkAction;
use App\Http\Requests\Admin\Video\BulkVideoActionRequest;
use Illuminate\Http\RedirectResponse;

class VideoBulkActionController
{
    public function __invoke(BulkVideoActionRequest $request, HandleVideoBulkAction $handler): RedirectResponse
    {
        $result = $handler->execute($request->bulkAction(), $request->videoIds());

        return redirect()->route('admin.videos.index')->with('success', $handler->successMessage($result));
    }
}

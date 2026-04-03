<?php

namespace App\Http\Controllers\Admin\Content;

use App\Application\Content\Actions\HandlePostBulkAction;
use App\Http\Requests\Admin\Content\BulkPostActionRequest;
use Throwable;

class PostBulkActionController
{
    /**
     * @throws Throwable
     */
    public function __invoke(
        BulkPostActionRequest $request,
        HandlePostBulkAction  $actionHandler
    )
    {
        $result = $actionHandler->execute(
            action: $request->bulkAction(),
            ids: $request->postIds(),
        );

        return redirect()
            ->route('admin.posts.index')
            ->with('success', $actionHandler->successMessage($result));
    }
}

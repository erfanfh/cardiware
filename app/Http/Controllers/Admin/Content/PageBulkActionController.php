<?php

namespace App\Http\Controllers\Admin\Content;

use App\Application\Content\Actions\HandlePageBulkAction;
use App\Http\Requests\Admin\Content\BulkPageActionRequest;
use Throwable;

class PageBulkActionController
{
    /**
     * @throws Throwable
     */
    public function __invoke(
        BulkPageActionRequest $request,
        HandlePageBulkAction  $actionHandler
    )
    {
        $result = $actionHandler->execute(
            action: $request->bulkAction(),
            ids: $request->pageIds(),
        );

        return redirect()
            ->route('admin.pages.index')
            ->with('success', $actionHandler->successMessage($result));
    }
}

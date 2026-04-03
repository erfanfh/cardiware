<?php

namespace App\Http\Controllers\Admin\Identity;

use App\Application\Project\Actions\HandleProjectBulkAction;
use App\Http\Requests\Admin\Project\BulkProjectActionRequest;
use Throwable;

class UserBulkActionController
{
    /**
     * @throws Throwable
     */
    public function __invoke(
        BulkProjectActionRequest $request,
        HandleProjectBulkAction  $actionHandler
    )
    {
        $result = $actionHandler->execute(
            action: $request->bulkAction(),
            ids: $request->projectIds(),
        );

        return redirect()
            ->route('admin.projects.index')
            ->with('success', $actionHandler->successMessage($result));
    }
}

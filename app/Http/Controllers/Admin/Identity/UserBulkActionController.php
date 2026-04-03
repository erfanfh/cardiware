<?php

namespace App\Http\Controllers\Admin\Identity;

use App\Application\Identity\Actions\User\HandleUserBulkAction;
use App\Http\Requests\Admin\Identity\BulkUserActionRequest;
use Throwable;

class UserBulkActionController
{
    /**
     * @throws Throwable
     */
    public function __invoke(
        BulkUserActionRequest $request,
        HandleUserBulkAction  $actionHandler
    )
    {
        $result = $actionHandler->execute(
            action: $request->bulkAction(),
            ids: $request->userIds(),
        );

        return redirect()
            ->route('admin.users.index')
            ->with('success', $actionHandler->successMessage($result));
    }
}

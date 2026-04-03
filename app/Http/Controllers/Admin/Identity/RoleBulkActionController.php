<?php

namespace App\Http\Controllers\Admin\Identity;

use App\Application\Identity\Actions\User\HandleRoleBulkAction;
use App\Http\Requests\Admin\Identity\BulkRoleActionRequest;
use Throwable;

class RoleBulkActionController
{
    /**
     * @throws Throwable
     */
    public function __invoke(
        BulkRoleActionRequest $request,
        HandleRoleBulkAction  $actionHandler
    )
    {
        $result = $actionHandler->execute(
            action: $request->bulkAction(),
            ids: $request->roleIds(),
        );

        return redirect()
            ->route('admin.roles.index')
            ->with('success', $actionHandler->successMessage($result));
    }
}

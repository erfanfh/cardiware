<?php

namespace App\Http\Controllers\Admin\Identity;

use App\Domain\Identity\Models\Permission;
use App\Http\Controllers\Admin\AdminSearchController;

class PermissionSearchController extends AdminSearchController
{
    protected function modelClass(): string
    {
        return Permission::class;
    }

    protected function permissionName(): string
    {
        return 'role.list';
    }

    protected function labelColumn(): string
    {
        return 'label';
    }
}

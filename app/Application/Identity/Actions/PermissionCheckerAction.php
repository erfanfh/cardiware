<?php

namespace App\Application\Identity\Actions;

use App\Domain\Identity\Models\Permission;
use App\Domain\Identity\Models\User;

class PermissionCheckerAction
{
    /**
     * Execute the permission check for a given group and ability.
     *
     * @param string $group
     * @param string $ability
     * @param User|null $user
     * @return bool
     */
    public function execute(string $group, string $ability, ?User $user = null): bool
    {
        if (!$user)
            $user = auth()->user();

        if ($user->role->is_super_admin)
            return true;

        if ($user->role->is_default)
            return false;

        $permission = Permission::where('group', $group)->where('ability', $ability)->first();

        if (!$permission)
            return false;

        $rolePermission = $user->role->permissions()->where('permission_id', $permission->id)->exists();

        if ($rolePermission)
            return true;

        return false;
    }
}

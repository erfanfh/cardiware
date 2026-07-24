<?php

namespace App\Application\Identity\Actions\Role;

use App\Application\Identity\Actions\GetDefaultRoleAction;
use App\Application\Identity\Exceptions\RegistrationRoleNotFoundException;
use App\Domain\Identity\Models\Role;

final class ResolveRegistrationRoleAction
{
    public function __construct(
        private readonly GetDefaultRoleAction $getDefaultRoleAction,
    )
    {
    }

    public function execute(?int $roleId = null): Role
    {
        $role = $roleId
            ? Role::query()->find($roleId)
            : $this->getDefaultRoleAction->execute();

        if (!$role) {
            throw new RegistrationRoleNotFoundException(
                'نقش پیش‌فرض کاربران پیدا نشد.'
            );
        }

        return $role;
    }
}

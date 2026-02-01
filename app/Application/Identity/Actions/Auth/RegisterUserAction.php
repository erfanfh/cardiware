<?php

namespace App\Application\Identity\Actions\Auth;

use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterUserAction
{
    /**
     * Registers a new user
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $mobile
     * @param string $password
     * @param int|null $roleId
     * @param bool $active
     * @return User
     */
    public function execute(
        string $firstName,
        string $lastName,
        string $mobile,
        string $password,
        bool   $active = true,
        ?int   $roleId = null
    ): User
    {
        $roleId = Role::find($roleId)?->id ?? Role::defaultRole()->id;

        return User::create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'mobile' => $mobile,
            'mobile_verified_at' => now(),
            'role_id' => $roleId,
            'password' => Hash::make($password),
            'active' => $active
        ]);
    }
}

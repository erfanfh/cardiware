<?php

namespace App\Application\Identity\Actions\Auth;

use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class RegisterUserAction
{
    /**
     * Registers a new user
     *
     * @param string $firstName
     * @param string $lastName
     * @param string $mobile
     * @param string $email
     * @param string $password
     * @param bool $active
     * @param int|null $roleId
     * @return User
     */
    public function execute(
        string $firstName,
        string $lastName,
        string $mobile,
        string $email,
        string $password,
        ?int   $roleId = null
    ): User
    {
        $roleId = Role::find($roleId)?->id ?? Role::defaultRole()->id;

        $data = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'display_name' => $firstName . ' ' . $lastName,
            'mobile' => $mobile,
            'email' => $email,
            'password' => Hash::make($password),
            'status' => UserStatusEnum::ACTIVE,
            'role_id' => $roleId,
        ];

        return User::create($data);
    }
}

<?php

namespace App\Application\Identity\Actions\User;

use App\Application\Identity\DTOs\RegisterUserDTO;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\Role;
use App\Domain\Identity\Models\User;

final class CreateUserAction
{
    public function execute(
        RegisterUserDTO $data,
        Role            $role,
        string          $username,
    ): User
    {
        $firstName = trim($data->firstName);
        $lastName = trim($data->lastName);

        $displayName = filled($data->displayName)
            ? trim($data->displayName)
            : $firstName . ' ' . $lastName;

        $email = filled($data->email)
            ? strtolower(trim($data->email))
            : null;

        return User::query()->create([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'display_name' => $displayName,
            'mobile' => $data->mobile,
            'email' => $email,
            'username' => $username,

            /*
             * مدل User دارای cast نوع hashed است.
             * بنابراین رمز را خام به مدل می‌دهیم و Laravel آن را Hash می‌کند.
             */
            'password' => $data->password,

            'status' => $data->status ?? UserStatusEnum::ACTIVE,
            'role_id' => $role->getKey(),
        ]);
    }
}

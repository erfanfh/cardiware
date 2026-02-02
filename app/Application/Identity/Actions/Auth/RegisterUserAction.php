<?php

namespace App\Application\Identity\Actions\Auth;

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
     * @param string $username
     * @param string $password
     * @param bool $active
     * @param int|null $roleId
     * @return User
     */
    public function execute(
        string $firstName,
        string $lastName,
        string $username,
        string $password,
        bool   $active = true,
        ?int   $roleId = null
    ): User
    {
        $roleId = Role::find($roleId)?->id ?? Role::defaultRole()->id;

        $isEmail  = filter_var($username, FILTER_VALIDATE_EMAIL);
        $isMobile = preg_match('/^09\d{9}$/', $username);

        if (! $isEmail && ! $isMobile) {
            throw new InvalidArgumentException('ایمیل یا شماره موبایل معتبر نیست');
        }

        $data = [
            'first_name' => $firstName,
            'last_name'  => $lastName,
            'password'   => Hash::make($password),
            'active'     => $active,
            'role_id'    => $roleId,
        ];

        if ($isEmail) {
            $data['email'] = $username;
            $data['email_verified_at'] = now();
        } else {
            $data['mobile'] = $username;
            $data['mobile_verified_at'] = now();
        }

        return User::create($data);
    }
}

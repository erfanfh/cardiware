<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Exceptions\InvalidCredentialsException;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Hash;

final class EnsureUserPasswordMatchesAction
{
    public function execute(
        User   $user,
        string $password,
    ): void
    {
        if (
            blank($user->password) ||
            !Hash::check($password, $user->getAuthPassword())
        ) {
            throw new InvalidCredentialsException(
                'اطلاعات وارد شده صحیح نیست.'
            );
        }
    }
}

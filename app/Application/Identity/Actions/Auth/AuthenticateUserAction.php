<?php

namespace App\Application\Identity\Actions\Auth;

use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Auth;

final class AuthenticateUserAction
{
    public function execute(
        User $user,
        bool $remember = false,
    ): void
    {
        Auth::login(
            user: $user,
            remember: $remember,
        );

        request()->session()->regenerate();
    }
}

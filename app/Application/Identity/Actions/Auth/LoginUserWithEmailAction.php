<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Exceptions\InvalidCredentialsException;
use App\Domain\Identity\Enums\AuthRateLimitScopeEnum;
use App\Domain\Identity\Enums\UserStatusEnum;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class LoginUserWithEmailAction
{
    public function __construct(
        private readonly ConsumeAuthRateLimitAction $consumeAuthRateLimitAction,
        private readonly ClearAuthRateLimitAction $clearAuthRateLimitAction,
        private readonly AuthenticateUserAction $authenticateUserAction,
    ) {}

    public function execute(string $email, string $password, bool $remember = true): User
    {
        $email = Str::lower(trim($email));

        // Count every attempt, including addresses that have never been registered.
        $this->consumeAuthRateLimitAction->execute(
            scope: AuthRateLimitScopeEnum::LOGIN_PASSWORD,
            identifier: $email,
        );

        $user = User::withTrashed()->where('email', $email)->first();
        $passwordMatches = false;

        if ($user && filled($user->getAuthPassword())) {
            $passwordMatches = Hash::check($password, $user->getAuthPassword());
        } else {
            // Perform password hashing even when the account/password is absent.
            Hash::make('email-login-dummy-password');
        }

        if (! $passwordMatches || ! $user || $user->trashed() || $user->status !== UserStatusEnum::ACTIVE) {
            throw new InvalidCredentialsException('اطلاعات وارد شده صحیح نیست.');
        }

        $this->authenticateUserAction->execute(user: $user, remember: $remember);

        $this->clearAuthRateLimitAction->execute(
            scope: AuthRateLimitScopeEnum::LOGIN_PASSWORD,
            identifier: $email,
        );

        return $user;
    }
}

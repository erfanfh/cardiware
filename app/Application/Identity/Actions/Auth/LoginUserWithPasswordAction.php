<?php

namespace App\Application\Identity\Actions\Auth;

use App\Domain\Identity\Enums\AuthRateLimitScopeEnum;
use App\Domain\Identity\Models\User;

final class LoginUserWithPasswordAction
{
    public function __construct(
        private readonly GetActiveUserByMobileAction     $getActiveUserByMobileAction,
        private readonly ConsumeAuthRateLimitAction      $consumeAuthRateLimitAction,
        private readonly ClearAuthRateLimitAction        $clearAuthRateLimitAction,
        private readonly EnsureUserPasswordMatchesAction $ensureUserPasswordMatchesAction,
        private readonly AuthenticateUserAction          $authenticateUserAction,
    )
    {
    }

    public function execute(
        string $mobile,
        string $password,
        bool   $remember = false,
    ): User
    {
        $user = $this->getActiveUserByMobileAction->execute(
            $mobile
        );

        $this->consumeAuthRateLimitAction->execute(
            scope: AuthRateLimitScopeEnum::LOGIN_PASSWORD,
            identifier: $user->mobile,
        );

        $this->ensureUserPasswordMatchesAction->execute(
            user: $user,
            password: $password,
        );

        $this->authenticateUserAction->execute(
            user: $user,
            remember: $remember,
        );

        $this->clearAuthRateLimitAction->execute(
            scope: AuthRateLimitScopeEnum::LOGIN_PASSWORD,
            identifier: $user->mobile,
        );

        return $user;
    }
}

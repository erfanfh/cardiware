<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Actions\Otp\ExecuteWithVerifiedOtpAction;
use App\Application\Identity\Exceptions\AccountUnavailableException;
use App\Application\Identity\Exceptions\PasswordUnchangedException;
use App\Domain\Identity\Enums\AuthRateLimitScopeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;
use App\Domain\Identity\Models\User;
use Illuminate\Support\Facades\Hash;

final class ChangeAccountPasswordWithOtpAction
{
    public function __construct(
        private readonly ConsumeAuthRateLimitAction $consumeAuthRateLimitAction,
        private readonly ClearAuthRateLimitAction $clearAuthRateLimitAction,
        private readonly ExecuteWithVerifiedOtpAction $executeWithVerifiedOtpAction,
        private readonly GetActiveUserByMobileAction $getActiveUserByMobileAction,
        private readonly UpdateUserPasswordAction $updateUserPasswordAction,
    ) {
    }

    public function execute(
        User $authenticatedUser,
        string $otpCode,
        string $password,
    ): User {
        $user = $this->getActiveUserByMobileAction->execute(
            (string) $authenticatedUser->mobile
        );

        if (! $user->is($authenticatedUser)) {
            throw new AccountUnavailableException(
                'امکان تغییر رمز عبور این حساب وجود ندارد.'
            );
        }

        $this->consumeAuthRateLimitAction->execute(
            scope: AuthRateLimitScopeEnum::CHANGE_ACCOUNT_PASSWORD,
            identifier: $user->mobile,
        );

        $user = $this->executeWithVerifiedOtpAction->execute(
            mobile: $user->mobile,
            code: $otpCode,
            otpType: OtpTypeEnum::ACCOUNT_PASSWORD_CHANGE,
            callback: function (Otp $otp) use ($user, $password): User {
                if (Hash::check($password, $user->getAuthPassword())) {
                    throw new PasswordUnchangedException(
                        'رمز عبور جدید نباید با رمز عبور فعلی یکسان باشد.'
                    );
                }

                return $this->updateUserPasswordAction->execute(
                    user: $user,
                    password: $password,
                );
            },
        );

        $this->clearAuthRateLimitAction->execute(
            scope: AuthRateLimitScopeEnum::CHANGE_ACCOUNT_PASSWORD,
            identifier: $user->mobile,
        );

        return $user;
    }
}

<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Actions\Mobile\NormalizeMobileAction;
use App\Application\Identity\Actions\Otp\ExecuteWithVerifiedOtpAction;
use App\Application\Identity\Actions\User\MarkUserMobileAsVerifiedAction;
use App\Domain\Identity\Enums\AuthRateLimitScopeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;
use App\Domain\Identity\Models\User;

final class LoginUserWithOtpAction
{
    public function __construct(
        private readonly NormalizeMobileAction          $normalizeMobileAction,
        private readonly ConsumeAuthRateLimitAction     $consumeAuthRateLimitAction,
        private readonly ClearAuthRateLimitAction       $clearAuthRateLimitAction,
        private readonly ExecuteWithVerifiedOtpAction   $executeWithVerifiedOtpAction,
        private readonly GetActiveUserByMobileAction    $getActiveUserByMobileAction,
        private readonly MarkUserMobileAsVerifiedAction $markUserMobileAsVerifiedAction,
        private readonly AuthenticateUserAction         $authenticateUserAction,
    )
    {
    }

    public function execute(
        string     $mobile,
        string|int $otpCode,
    ): User
    {
        $mobile = $this->normalizeMobileAction->execute($mobile);

        $this->consumeAuthRateLimitAction->execute(
            scope: AuthRateLimitScopeEnum::VERIFY_LOGIN_OTP,
            identifier: $mobile,
        );

        /** @var User $user */
        $user = $this->executeWithVerifiedOtpAction->execute(
            mobile: $mobile,
            code: $otpCode,
            otpType: OtpTypeEnum::LOGIN,
            callback: function (Otp $otp): User {
                $user = $this
                    ->getActiveUserByMobileAction
                    ->execute($otp->key);

                return $this
                    ->markUserMobileAsVerifiedAction
                    ->execute($user);
            },
        );

        /*
         * Session فقط بعد از Commit شدن تراکنش ساخته می‌شود.
         */
        $this->authenticateUserAction->execute($user);

        $this->clearAuthRateLimitAction->execute(
            scope: AuthRateLimitScopeEnum::VERIFY_LOGIN_OTP,
            identifier: $mobile,
        );

        return $user;
    }
}

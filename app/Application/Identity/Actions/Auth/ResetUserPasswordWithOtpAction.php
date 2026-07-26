<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Actions\Mobile\NormalizeMobileAction;
use App\Application\Identity\Actions\Otp\ExecuteWithVerifiedOtpAction;
use App\Application\Identity\Actions\User\MarkUserMobileAsVerifiedAction;
use App\Application\Identity\DTOs\ResetUserPasswordWithOtpDTO;
use App\Domain\Identity\Enums\AuthRateLimitScopeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;
use App\Domain\Identity\Models\User;

final class ResetUserPasswordWithOtpAction
{
    public function __construct(
        private readonly NormalizeMobileAction          $normalizeMobileAction,
        private readonly ConsumeAuthRateLimitAction     $consumeAuthRateLimitAction,
        private readonly ClearAuthRateLimitAction       $clearAuthRateLimitAction,
        private readonly ExecuteWithVerifiedOtpAction   $executeWithVerifiedOtpAction,
        private readonly GetActiveUserByMobileAction    $getActiveUserByMobileAction,
        private readonly UpdateUserPasswordAction       $updateUserPasswordAction,
        private readonly MarkUserMobileAsVerifiedAction $markUserMobileAsVerifiedAction,
        private readonly AuthenticateUserAction         $authenticateUserAction,
    )
    {
    }

    public function execute(
        ResetUserPasswordWithOtpDTO $data,
    ): User
    {
        $mobile = $this->normalizeMobileAction->execute(
            $data->mobile
        );

        $this->consumeAuthRateLimitAction->execute(
            scope: AuthRateLimitScopeEnum::RESET_PASSWORD,
            identifier: $mobile,
        );

        /** @var User $user */
        $user = $this->executeWithVerifiedOtpAction->execute(
            mobile: $mobile,
            code: $data->otpCode,
            otpType: OtpTypeEnum::RESET_PASSWORD,
            callback: function (Otp $otp) use ($data): User {
                $user = $this
                    ->getActiveUserByMobileAction
                    ->execute($otp->key);

                $user = $this->updateUserPasswordAction->execute(
                    user: $user,
                    password: $data->password,
                );

                return $this
                    ->markUserMobileAsVerifiedAction
                    ->execute($user);
            },
        );

        $this->authenticateUserAction->execute($user);

        $this->clearAuthRateLimitAction->execute(
            scope: AuthRateLimitScopeEnum::RESET_PASSWORD,
            identifier: $mobile,
        );

        return $user;
    }
}

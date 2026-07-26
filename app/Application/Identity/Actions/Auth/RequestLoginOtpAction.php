<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Actions\Otp\RequestOtpAction;
use App\Application\Identity\DTOs\SendOtpResultDTO;
use App\Domain\Identity\Enums\AuthRateLimitScopeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use Random\RandomException;

final class RequestLoginOtpAction
{
    public function __construct(
        private readonly GetActiveUserByMobileAction $getActiveUserByMobileAction,
        private readonly ConsumeAuthRateLimitAction  $consumeAuthRateLimitAction,
        private readonly RequestOtpAction            $requestOtpAction,
    )
    {
    }

    /**
     * @throws RandomException
     */
    public function execute(string $mobile): SendOtpResultDTO
    {
        $user = $this->getActiveUserByMobileAction->execute(
            $mobile
        );

        $this->consumeAuthRateLimitAction->execute(
            scope: AuthRateLimitScopeEnum::REQUEST_LOGIN_OTP,
            identifier: $user->mobile,
        );

        return $this->requestOtpAction->execute(
            mobile: $user->mobile,
            otpType: OtpTypeEnum::LOGIN,
        );
    }
}

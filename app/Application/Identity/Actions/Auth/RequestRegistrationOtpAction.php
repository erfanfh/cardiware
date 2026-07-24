<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Actions\Otp\RequestOtpAction;
use App\Application\Identity\DTOs\SendOtpResultDTO;
use App\Domain\Identity\Enums\AuthRateLimitScopeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use Random\RandomException;

final class RequestRegistrationOtpAction
{
    public function __construct(
        private readonly EnsureMobileAvailableForRegistrationAction $ensureMobileAvailableForRegistrationAction,
        private readonly ConsumeAuthRateLimitAction                 $consumeAuthRateLimitAction,
        private readonly RequestOtpAction                           $requestOtpAction,
    )
    {
    }

    /**
     * @throws RandomException
     */
    public function execute(string $mobile): SendOtpResultDTO
    {
        $mobile = $this
            ->ensureMobileAvailableForRegistrationAction
            ->execute($mobile);

        $this->consumeAuthRateLimitAction->execute(
            scope: AuthRateLimitScopeEnum::REQUEST_REGISTRATION_OTP,
            identifier: $mobile,
        );

        return $this->requestOtpAction->execute(
            mobile: $mobile,
            otpType: OtpTypeEnum::REGISTER,
        );
    }
}

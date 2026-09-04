<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Actions\Otp\RequestOtpAction;
use App\Application\Identity\DTOs\SendOtpResultDTO;
use App\Application\Identity\Exceptions\AccountUnavailableException;
use App\Domain\Identity\Enums\AuthRateLimitScopeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\User;
use Random\RandomException;

final class RequestAccountPasswordChangeOtpAction
{
    public function __construct(
        private readonly GetActiveUserByMobileAction $getActiveUserByMobileAction,
        private readonly ConsumeAuthRateLimitAction $consumeAuthRateLimitAction,
        private readonly RequestOtpAction $requestOtpAction,
    ) {
    }

    /**
     * @throws RandomException
     */
    public function execute(User $authenticatedUser): SendOtpResultDTO
    {
        $user = $this->getActiveUserByMobileAction->execute(
            (string) $authenticatedUser->mobile
        );

        if (! $user->is($authenticatedUser)) {
            throw new AccountUnavailableException(
                'امکان تایید شماره موبایل این حساب وجود ندارد.'
            );
        }

        $this->consumeAuthRateLimitAction->execute(
            scope: AuthRateLimitScopeEnum::REQUEST_ACCOUNT_PASSWORD_CHANGE_OTP,
            identifier: $user->mobile,
        );

        return $this->requestOtpAction->execute(
            mobile: $user->mobile,
            otpType: OtpTypeEnum::ACCOUNT_PASSWORD_CHANGE,
        );
    }
}

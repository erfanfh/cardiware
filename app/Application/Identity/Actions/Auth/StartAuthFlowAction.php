<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\DTOs\StartAuthFlowResultDTO;
use App\Application\Identity\Exceptions\OtpDeliveryFailedException;
use App\Domain\Identity\Enums\AuthFlowStepEnum;
use App\Domain\Identity\Enums\AuthRateLimitScopeEnum;
use Random\RandomException;

final class StartAuthFlowAction
{
    public function __construct(
        private readonly ResolveAuthMobileAction      $resolveAuthMobileAction,
        private readonly ConsumeAuthRateLimitAction   $consumeAuthRateLimitAction,
        private readonly RequestRegistrationOtpAction $requestRegistrationOtpAction,
    )
    {
    }

    /**
     * @throws RandomException
     */
    public function execute(
        string $mobile,
    ): StartAuthFlowResultDTO
    {
        $resolution = $this->resolveAuthMobileAction->execute(
            $mobile
        );

        $this->consumeAuthRateLimitAction->execute(
            scope: AuthRateLimitScopeEnum::START_FLOW,
            identifier: $resolution->mobile,
        );

        if ($resolution->userExists) {
            return new StartAuthFlowResultDTO(
                mobile: $resolution->mobile,
                nextStep: AuthFlowStepEnum::LOGIN_PASSWORD,
            );
        }

        $otpResult = $this
            ->requestRegistrationOtpAction
            ->execute($resolution->mobile);

        if (!$otpResult->sent && !$otpResult->alreadySent) {
            throw new OtpDeliveryFailedException(
                $otpResult->message
            );
        }

        return new StartAuthFlowResultDTO(
            mobile: $resolution->mobile,
            nextStep: AuthFlowStepEnum::REGISTER,
            otpResult: $otpResult,
        );
    }
}

<?php

namespace App\Application\Identity\DTOs;

use App\Domain\Identity\Enums\AuthFlowStepEnum;

final readonly class StartAuthFlowResultDTO
{
    public function __construct(
        public string            $mobile,
        public AuthFlowStepEnum  $nextStep,
        public ?SendOtpResultDTO $otpResult = null,
    )
    {
    }
}

<?php

namespace App\Application\Identity\Actions\Otp;

use App\Application\Identity\DTOs\OtpValidationResultDTO;
use App\Domain\Identity\Models\Otp;

final class ApplyOtpValidationFailureAction
{
    public function execute(
        ?Otp                   $otp,
        OtpValidationResultDTO $result,
    ): void
    {
        if (!$otp || $result->valid) {
            return;
        }

        $attempts = $otp->attempts;

        if ($result->incrementAttempts) {
            $attempts++;
        }

        $maxAttempts = max(
            1,
            (int)config(
                'auth-flow.otp.max_attempts',
                5
            )
        );

        $shouldInvalidate = $result->invalidateOtp || $attempts >= $maxAttempts;

        $otp->forceFill([
            'attempts' => $attempts,
            'is_used' => $shouldInvalidate,
        ])->save();
    }
}

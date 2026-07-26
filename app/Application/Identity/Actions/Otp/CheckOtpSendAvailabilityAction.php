<?php

namespace App\Application\Identity\Actions\Otp;

use App\Application\Identity\DTOs\OtpSendAvailabilityDTO;
use App\Domain\Identity\Enums\OtpKeyTypeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;

final class CheckOtpSendAvailabilityAction
{
    public function execute(
        string      $mobile,
        OtpTypeEnum $otpType,
    ): OtpSendAvailabilityDTO
    {
        $lastOtp = Otp::query()
            ->where('key', $mobile)
            ->where('key_type', OtpKeyTypeEnum::MOBILE->value)
            ->where('type', $otpType->value)
            ->where('is_used', false)
            ->latest('id')
            ->first();

        if (
            !$lastOtp ||
            !$lastOtp->resend_available_at ||
            $lastOtp->resend_available_at->isPast()
        ) {
            return new OtpSendAvailabilityDTO(
                canSend: true,
            );
        }

        $retryAfterSeconds = max(
            0,
            (int)now()->diffInSeconds(
                $lastOtp->resend_available_at
            )
        );

        $expiresAfterSeconds = $lastOtp->expired_at->isFuture()
            ? max(
                0,
                (int)now()->diffInSeconds(
                    $lastOtp->expired_at
                )
            )
            : 0;

        return new OtpSendAvailabilityDTO(
            canSend: false,
            retryAfterSeconds: $retryAfterSeconds,
            expiresAfterSeconds: $expiresAfterSeconds,
        );
    }
}

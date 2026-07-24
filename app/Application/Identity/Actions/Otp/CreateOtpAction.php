<?php

namespace App\Application\Identity\Actions\Otp;

use App\Application\Identity\Support\OtpCodeHasher;
use App\Domain\Identity\Enums\OtpKeyTypeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;

final class CreateOtpAction
{
    public function __construct(
        private readonly OtpCodeHasher $otpCodeHasher,
    )
    {
    }

    public function execute(
        string      $mobile,
        string      $code,
        OtpTypeEnum $otpType,
    ): Otp
    {
        $expiresAfterSeconds = max(
            60,
            (int)config(
                'auth-flow.otp.expires_after_seconds',
                300
            )
        );

        $resendAfterSeconds = max(
            30,
            (int)config(
                'auth-flow.otp.resend_after_seconds',
                300
            )
        );

        return Otp::query()->create([
            'key' => $mobile,
            'key_type' => OtpKeyTypeEnum::MOBILE,
            'type' => $otpType,
            'code' => null,
            'code_hash' => $this->otpCodeHasher->make($code),
            'attempts' => 0,
            'expired_at' => now()->addSeconds(
                $expiresAfterSeconds
            ),
            'resend_available_at' => now()->addSeconds(
                $resendAfterSeconds
            ),
            'is_used' => false,
        ]);
    }
}

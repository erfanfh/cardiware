<?php

namespace App\Application\Identity\Actions\Otp;

use App\Domain\Identity\Enums\OtpKeyTypeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;

final class LockLatestOtpAction
{
    /**
     * این Action باید داخل Database Transaction اجرا شود.
     */
    public function execute(
        string      $mobile,
        OtpTypeEnum $otpType,
    ): ?Otp
    {
        return Otp::query()
            ->where('key', $mobile)
            ->where(
                'key_type',
                OtpKeyTypeEnum::MOBILE->value
            )
            ->where('type', $otpType->value)
            ->where('is_used', false)
            ->latest('id')
            ->lockForUpdate()
            ->first();
    }
}

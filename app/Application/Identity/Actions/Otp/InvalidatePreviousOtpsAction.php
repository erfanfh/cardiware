<?php

namespace App\Application\Identity\Actions\Otp;

use App\Domain\Identity\Enums\OtpKeyTypeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;

final class InvalidatePreviousOtpsAction
{
    public function execute(
        string      $mobile,
        OtpTypeEnum $otpType,
        int         $exceptOtpId,
    ): void
    {
        Otp::query()
            ->where('key', $mobile)
            ->where('key_type', OtpKeyTypeEnum::MOBILE->value)
            ->where('type', $otpType->value)
            ->where('is_used', false)
            ->whereKeyNot($exceptOtpId)
            ->update([
                'is_used' => true,
            ]);
    }
}

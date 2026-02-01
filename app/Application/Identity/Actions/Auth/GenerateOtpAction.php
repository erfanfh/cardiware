<?php

namespace App\Application\Identity\Actions\Auth;

use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;
use App\Infrastructure\Services\Sms\SmsService;

class GenerateOtpAction
{
    /**
     * @param $mobile
     * @param OtpTypeEnum $otpType
     * @return bool
     */
    public function execute($mobile, OtpTypeEnum $otpType): bool
    {
        $lastOtp = Otp::where('mobile', $mobile)->where('is_used', 0)->latest()->first();

        if ($lastOtp && $lastOtp->created_at > now()->subSeconds(60)) {
            return false;
        }

        $code = rand(100000, 999999);

        Otp::create([
            'mobile' => $mobile,
            'code' => $code,
            'expired_at' => now()->addMinutes(5),
            'type' => $otpType,
        ]);
        (new SmsService())->send($mobile, $code);

        return true;
    }
}

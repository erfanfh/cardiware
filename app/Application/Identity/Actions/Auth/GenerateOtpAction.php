<?php

namespace App\Application\Identity\Actions\Auth;

use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;
use App\Infrastructure\Services\Sms\SmsService;

class GenerateOtpAction
{
    /**
     * @param string $username
     * @param OtpTypeEnum $otpType
     * @return bool
     */
    public function execute(string $username, OtpTypeEnum $otpType): bool
    {
        $isEmail = filter_var($username, FILTER_VALIDATE_EMAIL);
        $isMobile = preg_match('/^09\d{9}$/', $username);

        if (!$isEmail && !$isMobile) {
            return false;
        }

        $lastOtp = Otp::where('key', $username)
            ->where('is_used', 0)
            ->latest()
            ->first();

        if ($lastOtp && $lastOtp->created_at > now()->subSeconds(60)) {
            return false;
        }

        $code = rand(100000, 999999);

        Otp::create([
            'key' => $username,
            'code' => $code,
            'expired_at' => now()->addMinutes(5),
            'type' => $otpType,
        ]);

        if ($isMobile) {
            (new SmsService())->send($username, $code);
        }

        if ($isEmail) {
            //send email
        }

        return true;
    }
}

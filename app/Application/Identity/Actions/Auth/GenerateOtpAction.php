<?php

namespace App\Application\Identity\Actions\Auth;

use App\Domain\Identity\Enums\OtpKeyTypeEnum;
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

        $code = rand(config('auth.otp.min'), config('auth.otp.max'));

        Otp::create([
            'key' => $username,
            'key_type' => $isEmail ? OtpKeyTypeEnum::EMAIL->value : OtpKeyTypeEnum::MOBILE->value,
            'code' => $code,
            'expired_at' => now()->addMinutes(config('auth.otp.expire')),
            'type' => $otpType,
        ]);

        if ($isMobile) {
            (new SmsService())->send($username, $code);
        } elseif ($isEmail) {
            //send email
        }

        return true;
    }
}

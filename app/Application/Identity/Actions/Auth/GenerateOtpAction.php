<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Mails\Auth\OtpMail;
use App\Domain\Identity\Enums\OtpKeyTypeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;
use App\Infrastructure\Services\Sms\SmsService;
use Illuminate\Support\Facades\Mail;
use Random\RandomException;

class GenerateOtpAction
{
    /**
     * @param string $username
     * @param OtpTypeEnum $otpType
     * @return bool
     * @throws RandomException
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

        $code = random_int(10 ** (config('auth.otp.length') - 1), (10 ** config('auth.otp.length')) - 1);

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
            Mail::to($username)->send(new OtpMail($code));
        }

        return true;
    }
}

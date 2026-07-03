<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\DTOs\SendOtpResultDTO;
use App\Domain\Identity\Enums\OtpKeyTypeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;
use App\Infrastructure\Services\Sms\SmsService;
use Random\RandomException;

class SendOtpAction
{
    /**
     * @param string $key
     * @param OtpTypeEnum $otpType
     * @return SendOtpResultDTO
     * @throws RandomException
     */
    public function execute(string $key, OtpTypeEnum $otpType): SendOtpResultDTO
    {
        $isEmail = filter_var($key, FILTER_VALIDATE_EMAIL);
        $isMobile = preg_match('/^09\d{9}$/', $key);

        if (!$isMobile) {
            return new SendOtpResultDTO(
                sent: false,
                alreadySent: false,
                message: 'شماره موبایل وارد شده صحیح نمی باشد.',
            );
        }

        $lastOtp = Otp::where('key', $key)
            ->where('is_used', 0)
            ->latest()
            ->first();

        if ($lastOtp && $lastOtp->created_at > now()->subSeconds(config('auth.otp.resend_seconds'))) {
            return new SendOtpResultDTO(
                sent: false,
                alreadySent: true,
                message: 'کد تایید قبلا برای شما ارسال شده است. برای ارسال مجدد کمی صبر کنید',
            );
        }

        $code = random_int(10 ** (config('auth.otp.length') - 1), (10 ** config('auth.otp.length')) - 1);

        Otp::create([
            'key' => $key,
            'key_type' => $isEmail ? OtpKeyTypeEnum::EMAIL : OtpKeyTypeEnum::MOBILE,
            'code' => $code,
            'expired_at' => now()->addSeconds(config('auth.otp.expire_seconds')),
            'type' => $otpType,
        ]);

        (new SmsService())->sendOtp($key, $code);

        return new SendOtpResultDTO(
            sent: true,
            alreadySent: false,
            message: 'کد یکبارمصرف با موفقیت ارسال شد.',
        );
    }
}

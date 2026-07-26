<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\DTOs\SendOtpResultDTO;
use App\Application\Identity\Support\OtpCodeHasher;
use App\Domain\Identity\Enums\OtpKeyTypeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;
use App\Infrastructure\Services\Sms\SmsService;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Support\Facades\Cache;
use Random\RandomException;
use Throwable;

final class SendOtpAction
{
    public function __construct(
        private readonly SmsService    $smsService,
        private readonly OtpCodeHasher $otpCodeHasher,
    )
    {
    }

    /**
     * @throws RandomException
     */
    public function execute(
        string      $key,
        OtpTypeEnum $otpType,
    ): SendOtpResultDTO
    {
        $mobile = normalize_number(trim($key));

        if (!preg_match('/^09\d{9}$/', $mobile)) {
            return new SendOtpResultDTO(
                sent: false,
                alreadySent: false,
                message: 'شماره موبایل وارد شده صحیح نمی‌باشد.',
            );
        }

        $lock = Cache::lock(
            $this->lockKey($mobile, $otpType),
            10
        );

        if (!$lock->get()) {
            return new SendOtpResultDTO(
                sent: false,
                alreadySent: true,
                message: 'درخواست ارسال کد در حال پردازش است.',
                retryAfterSeconds: 1,
            );
        }

        try {
            return $this->send(
                mobile: $mobile,
                otpType: $otpType,
            );
        } finally {
            $this->releaseLock($lock);
        }
    }

    /**
     * @throws RandomException
     */
    private function send(
        string      $mobile,
        OtpTypeEnum $otpType,
    ): SendOtpResultDTO
    {
        $lastOtp = Otp::query()
            ->where('key', $mobile)
            ->where('key_type', OtpKeyTypeEnum::MOBILE->value)
            ->where('type', $otpType->value)
            ->where('is_used', false)
            ->latest('id')
            ->first();

        if (
            $lastOtp &&
            $lastOtp->resend_available_at?->isFuture()
        ) {
            return new SendOtpResultDTO(
                sent: false,
                alreadySent: true,
                message: 'کد تایید قبلا برای شما ارسال شده است.',
                retryAfterSeconds: now()->diffInSeconds(
                    $lastOtp->resend_available_at
                ),
                expiresAfterSeconds: $lastOtp->expired_at->isFuture()
                    ? now()->diffInSeconds($lastOtp->expired_at)
                    : 0,
            );
        }

        $length = $this->otpLength();
        $expiresAfterSeconds = $this->expiresAfterSeconds();
        $resendAfterSeconds = $this->resendAfterSeconds();

        $code = (string)random_int(
            10 ** ($length - 1),
            (10 ** $length) - 1
        );

        $otp = Otp::query()->create([
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

        try {
            $this->smsService->sendOtp(
                $mobile,
                $code
            );
        } catch (Throwable $exception) {
            $otp->delete();

            report($exception);

            return new SendOtpResultDTO(
                sent: false,
                alreadySent: false,
                message: 'ارسال کد تایید با خطا مواجه شد. دوباره تلاش کنید.',
            );
        }

        /*
         * OTPهای قبلی همین Flow دیگر قابل استفاده نیستند.
         *
         * اگر این Query به هر دلیل شکست بخورد، OTP جدید همچنان آخرین OTP
         * است و فقط همان OTP توسط اعتبارسنجی بررسی خواهد شد.
         */
        try {
            Otp::query()
                ->where('key', $mobile)
                ->where('key_type', OtpKeyTypeEnum::MOBILE->value)
                ->where('type', $otpType->value)
                ->where('is_used', false)
                ->where('id', '!=', $otp->getKey())
                ->update([
                    'is_used' => true,
                ]);
        } catch (Throwable $exception) {
            report($exception);
        }

        return new SendOtpResultDTO(
            sent: true,
            alreadySent: false,
            message: 'کد یکبار مصرف با موفقیت ارسال شد.',
            retryAfterSeconds: $resendAfterSeconds,
            expiresAfterSeconds: $expiresAfterSeconds,
        );
    }

    private function otpLength(): int
    {
        return min(
            8,
            max(
                4,
                (int)config('auth-flow.otp.length', 6)
            )
        );
    }

    private function expiresAfterSeconds(): int
    {
        return max(
            60,
            (int)config(
                'auth-flow.otp.expires_after_seconds',
                300
            )
        );
    }

    private function resendAfterSeconds(): int
    {
        return max(
            30,
            (int)config(
                'auth-flow.otp.resend_after_seconds',
                300
            )
        );
    }

    private function lockKey(
        string      $mobile,
        OtpTypeEnum $otpType,
    ): string
    {
        return 'otp:send:' . hash(
                'sha256',
                $mobile . '|' . $otpType->value
            );
    }

    private function releaseLock(Lock $lock): void
    {
        try {
            $lock->release();
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}

<?php

namespace App\Application\Identity\Actions\Auth;

use App\Application\Identity\Exceptions\InvalidMobileException;
use App\Application\Identity\Exceptions\InvalidOtpException;
use App\Application\Identity\Support\OtpCodeHasher;
use App\Domain\Identity\Enums\OtpKeyTypeEnum;
use App\Domain\Identity\Enums\OtpTypeEnum;
use App\Domain\Identity\Models\Otp;
use Closure;
use Illuminate\Support\Facades\DB;

final class ConsumeOtpAction
{
    public function __construct(
        private readonly OtpCodeHasher $otpCodeHasher,
    )
    {
    }

    /**
     * Callback فقط در صورت معتبر بودن OTP اجرا می‌شود.
     *
     * @template TResult
     *
     * @param Closure(Otp): TResult $callback
     *
     * @return TResult
     */
    public function execute(
        string      $key,
        string|int  $code,
        OtpTypeEnum $otpType,
        Closure     $callback,
    ): mixed
    {
        $mobile = normalize_number(trim($key));
        $code = normalize_number(trim((string)$code));

        if (!preg_match('/^09\d{9}$/', $mobile)) {
            throw new InvalidMobileException(
                'شماره موبایل معتبر نیست.'
            );
        }

        $otpLength = (int)config(
            'auth-flow.otp.length',
            6
        );

        if (!preg_match('/^\d{' . $otpLength . '}$/', $code)) {
            throw new InvalidOtpException(
                'کد تایید وارد شده معتبر نیست.'
            );
        }

        $result = DB::transaction(function () use (
            $mobile,
            $code,
            $otpType,
            $callback,
        ): array {
            $otp = Otp::query()
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

            if (!$otp) {
                return [
                    'verified' => false,
                    'message' => 'کد تایید معتبر نیست یا قبلا استفاده شده است.',
                    'value' => null,
                ];
            }

            if ($otp->expired_at->isPast()) {
                $otp->forceFill([
                    'is_used' => true,
                ])->save();

                return [
                    'verified' => false,
                    'message' => 'زمان اعتبار کد تایید به پایان رسیده است.',
                    'value' => null,
                ];
            }

            $maxAttempts = max(
                1,
                (int)config(
                    'auth-flow.otp.max_attempts',
                    5
                )
            );

            if ($otp->attempts >= $maxAttempts) {
                $otp->forceFill([
                    'is_used' => true,
                ])->save();

                return [
                    'verified' => false,
                    'message' => 'تعداد تلاش‌های ناموفق بیش از حد مجاز است. کد جدید دریافت کنید.',
                    'value' => null,
                ];
            }

            if (
                !$this->otpCodeHasher->check(
                    $code,
                    $otp->code_hash
                )
            ) {
                $attempts = $otp->attempts + 1;

                $otp->forceFill([
                    'attempts' => $attempts,
                    'is_used' => $attempts >= $maxAttempts,
                ])->save();

                return [
                    'verified' => false,
                    'message' => $attempts >= $maxAttempts
                        ? 'تعداد تلاش‌های ناموفق بیش از حد مجاز است. کد جدید دریافت کنید.'
                        : 'کد تایید وارد شده صحیح نیست.',
                    'value' => null,
                ];
            }

            $otp->forceFill([
                'is_used' => true,
            ])->save();

            return [
                'verified' => true,
                'message' => '',
                'value' => $callback($otp),
            ];
        });

        /*
         * Exception بعد از Commit پرتاب می‌شود تا افزایش attempts
         * در صورت کد اشتباه Rollback نشود.
         */
        if (!$result['verified']) {
            throw new InvalidOtpException(
                $result['message']
            );
        }

        return $result['value'];
    }
}

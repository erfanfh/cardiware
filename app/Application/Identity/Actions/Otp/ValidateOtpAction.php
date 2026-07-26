<?php

namespace App\Application\Identity\Actions\Otp;

use App\Application\Identity\DTOs\OtpValidationResultDTO;
use App\Application\Identity\Support\OtpCodeHasher;
use App\Domain\Identity\Models\Otp;

final class ValidateOtpAction
{
    public function __construct(
        private readonly OtpCodeHasher $otpCodeHasher,
    )
    {
    }

    public function execute(
        ?Otp   $otp,
        string $code,
    ): OtpValidationResultDTO
    {
        if (!$otp) {
            return OtpValidationResultDTO::invalid(
                message: 'کد تایید معتبر نیست یا قبلا استفاده شده است.'
            );
        }

        if ($otp->expired_at->isPast()) {
            return OtpValidationResultDTO::invalid(
                message: 'زمان اعتبار کد تایید به پایان رسیده است.',
                invalidateOtp: true,
            );
        }

        $maxAttempts = max(
            1,
            (int)config(
                'auth-flow.otp.max_attempts',
                5
            )
        );

        if ($otp->attempts >= $maxAttempts) {
            return OtpValidationResultDTO::invalid(
                message: 'تعداد تلاش‌های ناموفق بیش از حد مجاز است. کد جدید دریافت کنید.',
                invalidateOtp: true,
            );
        }

        if (
            !$this->otpCodeHasher->check(
                code: $code,
                codeHash: $otp->code_hash,
            )
        ) {
            return OtpValidationResultDTO::invalid(
                message: 'کد تایید وارد شده صحیح نیست.',
                incrementAttempts: true,
            );
        }

        return OtpValidationResultDTO::valid();
    }
}

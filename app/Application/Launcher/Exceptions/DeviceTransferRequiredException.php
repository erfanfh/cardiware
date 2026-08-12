<?php

namespace App\Application\Launcher\Exceptions;

use App\Infrastructure\Exceptions\ApiException;

final class DeviceTransferRequiredException extends ApiException
{
    public function __construct(bool $otpSent, ?int $retryAfterSeconds = null)
    {
        parent::__construct(
            message: $otpSent
                ? 'این حساب روی دستگاه دیگری فعال است. کد انتقال برای شماره موبایل حساب ارسال شد.'
                : 'کد انتقال قبلاً ارسال شده است. کد را وارد کنید یا پس از پایان زمان انتظار دوباره تلاش کنید.',
            errorCode: 'device_transfer_required',
            statusCode: 409,
            details: [
                'otp_sent' => $otpSent,
                'retry_after_seconds' => $retryAfterSeconds,
                'transfer_is_final' => true,
            ],
        );
    }
}

<?php

namespace App\Application\Launcher\Exceptions;

use App\Infrastructure\Exceptions\ApiException;

class DeviceFingerprintMismatchException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            message: 'شناسه سخت‌افزاری این دستگاه با ثبت قبلی مطابقت ندارد.',
            errorCode: 'device_fingerprint_mismatch',
            statusCode: 409,
        );
    }
}

<?php

namespace App\Application\Launcher\Exceptions;

use App\Infrastructure\Exceptions\ApiException;

class DeviceLimitReachedException extends ApiException
{
    public function __construct(int $maximum, array $devices)
    {
        parent::__construct(
            message: 'حداکثر تعداد دستگاه فعال تکمیل شده است.',
            errorCode: 'device_limit_reached',
            statusCode: 409,
            details: ['maximum' => $maximum, 'devices' => $devices],
        );
    }
}

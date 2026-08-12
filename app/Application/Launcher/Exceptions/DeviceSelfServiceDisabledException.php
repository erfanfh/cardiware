<?php

namespace App\Application\Launcher\Exceptions;

use App\Infrastructure\Exceptions\ApiException;

final class DeviceSelfServiceDisabledException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            message: 'حذف یا جایگزینی دستگاه فقط توسط پشتیبانی امکان‌پذیر است.',
            errorCode: 'device_self_service_disabled',
            statusCode: 403,
        );
    }
}

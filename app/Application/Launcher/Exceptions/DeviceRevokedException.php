<?php

namespace App\Application\Launcher\Exceptions;

use App\Infrastructure\Exceptions\ApiException;

class DeviceRevokedException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            message: 'دسترسی این دستگاه لغو شده است.',
            errorCode: 'device_revoked',
            statusCode: 401,
        );
    }
}

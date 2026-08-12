<?php

namespace App\Application\Launcher\Exceptions;

use App\Infrastructure\Exceptions\ApiException;

final class InvalidDeviceProofException extends ApiException
{
    public function __construct(string $message = 'امضای امنیتی دستگاه معتبر نیست.')
    {
        parent::__construct(
            message: $message,
            errorCode: 'invalid_device_proof',
            statusCode: 422,
        );
    }
}

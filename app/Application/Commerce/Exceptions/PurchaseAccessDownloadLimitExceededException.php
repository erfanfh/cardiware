<?php

namespace App\Application\Commerce\Exceptions;


use App\Infrastructure\Exceptions\ApiException;

class PurchaseAccessDownloadLimitExceededException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            message: 'محدودیت دانلود این محصول به پایان رسیده است.',
            errorCode: 'download_limit_exceeded',
            statusCode: 403,
        );
    }
}

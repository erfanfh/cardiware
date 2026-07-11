<?php

namespace App\Application\Commerce\Exceptions;

use App\Infrastructure\Exceptions\ApiException;

class PurchaseAccessFileUnavailableException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            message: 'فایل موردنظر در حال حاضر قابل دانلود نیست.',
            errorCode: 'download_file_unavailable',
            statusCode: 503,
        );
    }
}

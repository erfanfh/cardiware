<?php

namespace App\Application\Commerce\Exceptions;

use RuntimeException;

class PurchaseAccessFileMissingException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct(
            'فایل موردنظر در حال حاضر در دسترس نیست.'
        );
    }
}

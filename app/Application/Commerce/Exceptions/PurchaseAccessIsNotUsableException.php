<?php

namespace App\Application\Commerce\Exceptions;

use App\Infrastructure\Exceptions\ApiException;

class PurchaseAccessIsNotUsableException extends ApiException
{
    public function __construct()
    {
        parent::__construct(
            message: 'دسترسی شما به این محصول فعال نیست یا منقضی شده است.',
            errorCode: 'purchase_access_not_usable',
            statusCode: 403,
        );
    }
}

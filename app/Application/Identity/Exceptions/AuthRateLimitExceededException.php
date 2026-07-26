<?php

namespace App\Application\Identity\Exceptions;

use DomainException;

final class AuthRateLimitExceededException extends DomainException
{
    public function __construct(
        public readonly int $retryAfterSeconds,
        string              $message = 'تعداد درخواست‌ها بیش از حد مجاز است.',
    )
    {
        parent::__construct($message);
    }
}

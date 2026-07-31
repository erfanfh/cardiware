<?php

namespace App\Application\Launcher\Exceptions;

use App\Infrastructure\Exceptions\ApiException;

class LauncherCommerceException extends ApiException
{
    public static function notFree(): self
    {
        return new self('این نسخه رایگان نیست.', 'not_free', 409);
    }

    public static function checkoutExpired(): self
    {
        return new self('نشست خرید منقضی یا قبلاً مصرف شده است.', 'checkout_session_invalid', 409);
    }

    public static function purchaseNotCompleted(): self
    {
        return new self('خرید هنوز در سرور تأیید نشده است.', 'purchase_not_completed', 409);
    }

    private function __construct(string $message, string $code, int $status)
    {
        parent::__construct($message, $code, $status);
    }
}

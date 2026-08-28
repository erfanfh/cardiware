<?php

namespace App\Application\Loyalty\PointConversion\Exceptions;

use DomainException;

class PointConversionException extends DomainException
{
    public static function unavailable(): self
    {
        return new self('تبدیل امتیاز به کیف پول در حال حاضر فعال نیست.');
    }

    public static function invalidAmount(string $message): self
    {
        return new self($message);
    }

    public static function dailyLimitReached(): self
    {
        return new self('سقف تبدیل روزانه شما تکمیل شده است. لطفاً روز دیگری دوباره تلاش کنید.');
    }

    public static function walletUnavailable(): self
    {
        return new self('کیف پول شما در حال حاضر امکان دریافت اعتبار جدید را ندارد.');
    }

    public static function idempotencyConflict(): self
    {
        return new self('شناسه این درخواست قبلاً برای مقدار دیگری استفاده شده است.');
    }
}

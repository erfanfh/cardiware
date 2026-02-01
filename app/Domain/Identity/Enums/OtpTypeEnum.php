<?php

namespace App\Domain\Identity\Enums;

enum OtpTypeEnum: string
{
    case REGISTER = 'register';
    case FORGOT_PASSWORD = 'forgot_password';

    public function label(): string
    {
        return match ($this) {
            self::REGISTER => 'ثبت نام',
            self::FORGOT_PASSWORD => 'فراموشی رمز عبور',
        };
    }
}

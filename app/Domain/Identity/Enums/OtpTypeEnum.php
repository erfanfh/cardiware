<?php

namespace App\Domain\Identity\Enums;

enum OtpTypeEnum: string
{
    case REGISTER = 'register';
    case LOGIN = 'login';
    case RESET_PASSWORD = 'reset_password';

    public function label(): string
    {
        return match ($this) {
            self::REGISTER => 'ثبت نام',
            self::RESET_PASSWORD => 'فراموشی رمز عبور',
            self::LOGIN => 'ورود',
        };
    }
}

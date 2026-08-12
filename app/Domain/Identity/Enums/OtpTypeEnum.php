<?php

namespace App\Domain\Identity\Enums;

enum OtpTypeEnum: string
{
    case REGISTER = 'register';
    case LOGIN = 'login';
    case RESET_PASSWORD = 'reset_password';
    case DEVICE_TRANSFER = 'device_transfer';

    public function label(): string
    {
        return match ($this) {
            self::REGISTER => 'ثبت نام',
            self::RESET_PASSWORD => 'فراموشی رمز عبور',
            self::LOGIN => 'ورود',
            self::DEVICE_TRANSFER => 'انتقال دستگاه لانچر',
        };
    }
}

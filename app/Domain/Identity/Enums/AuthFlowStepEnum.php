<?php

namespace App\Domain\Identity\Enums;

enum AuthFlowStepEnum: string
{
    case LOGIN = 'login';
    case REGISTER = 'register';
    case OTP_LOGIN = 'otp_login';
    case VERIFY_OTP_LOGIN = 'verify_otp_login';
    case ENTER_USERNAME = 'enter_username';
    case FORGOT_PASSWORD = 'forgot_password';
}

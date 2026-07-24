<?php

namespace App\Domain\Identity\Enums;

enum AuthFlowStepEnum: string
{
    case ENTER_MOBILE = 'enter_mobile';

    case LOGIN_PASSWORD = 'login_password';

    case VERIFY_LOGIN_OTP = 'verify_login_otp';

    case REGISTER = 'register';

    case RESET_PASSWORD = 'reset_password';
}

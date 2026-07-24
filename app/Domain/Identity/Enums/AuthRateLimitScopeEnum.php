<?php

namespace App\Domain\Identity\Enums;

enum AuthRateLimitScopeEnum: string
{
    case START_FLOW = 'start_flow';

    case LOGIN_PASSWORD = 'login_password';

    case REQUEST_LOGIN_OTP = 'request_login_otp';

    case REQUEST_REGISTRATION_OTP = 'request_registration_otp';

    case REQUEST_PASSWORD_RESET_OTP = 'request_password_reset_otp';

    case VERIFY_LOGIN_OTP = 'verify_login_otp';

    case REGISTER = 'register';

    case RESET_PASSWORD = 'reset_password';
}

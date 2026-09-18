<?php

namespace App\Domain\Identity\Enums;

enum AuthRateLimitScopeEnum: string
{
    case START_FLOW = 'start_flow';

    case LOGIN_PASSWORD = 'login_password';

    case REQUEST_LOGIN_OTP = 'request_login_otp';

    case REQUEST_REGISTRATION_OTP = 'request_registration_otp';

    case REQUEST_PASSWORD_RESET_OTP = 'request_password_reset_otp';

    case REQUEST_ACCOUNT_PASSWORD_CHANGE_OTP = 'request_account_password_change_otp';

    case VERIFY_LOGIN_OTP = 'verify_login_otp';

    case REGISTER = 'register';

    case RESET_PASSWORD = 'reset_password';

    case CHANGE_ACCOUNT_PASSWORD = 'change_account_password';
}

<?php

return [

    'otp' => [
        'length' => (int)env('AUTH_OTP_LENGTH', 6),

        'expires_after_seconds' => (int)env(
            'AUTH_OTP_EXPIRES_AFTER_SECONDS',
            300
        ),

        'resend_after_seconds' => (int)env(
            'AUTH_OTP_RESEND_AFTER_SECONDS',
            300
        ),

        'max_attempts' => (int)env(
            'AUTH_OTP_MAX_ATTEMPTS',
            5
        ),
    ],

    'rate_limits' => [

        'start_flow' => [
            'max_attempts' => 10,
            'decay_seconds' => 60,
        ],

        'login_password' => [
            'max_attempts' => 5,
            'decay_seconds' => 300,
        ],

        'request_login_otp' => [
            'max_attempts' => 5,
            'decay_seconds' => 300,
        ],

        'request_registration_otp' => [
            'max_attempts' => 5,
            'decay_seconds' => 300,
        ],

        'request_password_reset_otp' => [
            'max_attempts' => 5,
            'decay_seconds' => 300,
        ],

        'verify_login_otp' => [
            'max_attempts' => 10,
            'decay_seconds' => 300,
        ],

        'register' => [
            'max_attempts' => 10,
            'decay_seconds' => 300,
        ],

        'reset_password' => [
            'max_attempts' => 10,
            'decay_seconds' => 300,
        ],

    ],

];

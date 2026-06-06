<?php

return [
    'gateways' => [
        'zibal' => [
            'merchant' => env('ZIBAL_MERCHANT', 'zibal'),

            'request_url' => env('ZIBAL_REQUEST_URL', 'https://gateway.zibal.ir/v1/request'),
            'verify_url' => env('ZIBAL_VERIFY_URL', 'https://gateway.zibal.ir/v1/verify'),
            'start_url' => env('ZIBAL_START_URL', 'https://gateway.zibal.ir/start/'),

            'timeout' => env('ZIBAL_TIMEOUT', 20),
        ],

        'zarinpal' => [
            'merchant_id' => env('ZARINPAL_MERCHANT_ID'),

            'sandbox' => env('ZARINPAL_SANDBOX', true),

            'production' => [
                'request_url' => 'https://payment.zarinpal.com/pg/v4/payment/request.json',
                'verify_url' => 'https://payment.zarinpal.com/pg/v4/payment/verify.json',
                'start_url' => 'https://payment.zarinpal.com/pg/StartPay/',
            ],

            'sandbox_urls' => [
                'request_url' => 'https://sandbox.zarinpal.com/pg/v4/payment/request.json',
                'verify_url' => 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json',
                'start_url' => 'https://sandbox.zarinpal.com/pg/StartPay/',
            ],

            /**
             * Internal system amounts are stored in toman.
             * Zarinpal request/verify amount should be sent in rial unless you explicitly use currency.
             */
            'amount_multiplier' => env('ZARINPAL_AMOUNT_MULTIPLIER', 10),

            'timeout' => env('ZARINPAL_TIMEOUT', 20),
        ],

        'payping' => [
            'token' => env('PAYPING_TOKEN'),

            /*
            |--------------------------------------------------------------------------
            | PayPing Sandbox
            |--------------------------------------------------------------------------
            |
            | PayPing usually works with token-based API access.
            | If PayPing provides a separate test endpoint/token, set PAYPING_SANDBOX=true
            | and override the sandbox URLs in .env.
            |
            */
            'sandbox' => env('PAYPING_SANDBOX', false),

            'production' => [
                'pay_url' => env('PAYPING_PAY_URL', 'https://api.payping.ir/v3/pay'),
                'verify_url' => env('PAYPING_VERIFY_URL', 'https://api.payping.ir/v3/pay/verify'),
                'start_url' => env('PAYPING_START_URL', 'https://api.payping.ir/new/v2/pay/gotoipg/'),
            ],

            'sandbox_urls' => [
                'pay_url' => env('PAYPING_SANDBOX_PAY_URL', 'https://api.payping.ir/v3/pay'),
                'verify_url' => env('PAYPING_SANDBOX_VERIFY_URL', 'https://api.payping.ir/v3/pay/verify'),
                'start_url' => env('PAYPING_SANDBOX_START_URL', 'https://api.payping.ir/new/v2/pay/gotoipg/'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Amount Unit
            |--------------------------------------------------------------------------
            |
            | Your internal system stores amounts in toman.
            | If your PayPing account expects toman, keep this 1.
            | If it expects rial, set it to 10.
            |
            */
            'amount_multiplier' => env('PAYPING_AMOUNT_MULTIPLIER', 1),

            'timeout' => env('PAYPING_TIMEOUT', 20),
        ],
    ],
];

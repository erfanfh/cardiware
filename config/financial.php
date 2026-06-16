<?php

return [
    'gateways' => [
        'zibal' => [
            'request_url' => 'https://gateway.zibal.ir/v1/request',
            'verify_url' => 'https://gateway.zibal.ir/v1/verify',
            'start_url' => 'https://gateway.zibal.ir/start/',

            'timeout' => 20,
        ],

        'zarinpal' => [
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
            'amount_multiplier' => 10,

            'timeout' => 20,
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
                'pay_url' => 'https://api.payping.ir/v3/pay',
                'verify_url' => 'https://api.payping.ir/v3/pay/verify',
                'start_url' => 'https://api.payping.ir/new/v2/pay/gotoipg/',
            ],

            'sandbox_urls' => [
                'pay_url' => 'https://api.payping.ir/v3/pay',
                'verify_url' => 'https://api.payping.ir/v3/pay/verify',
                'start_url' => 'https://api.payping.ir/new/v2/pay/gotoipg/',
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
            'amount_multiplier' => 1,

            'timeout' => 20,
        ],
    ],
];

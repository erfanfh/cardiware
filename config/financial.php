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
    ],
];

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
    ],
];

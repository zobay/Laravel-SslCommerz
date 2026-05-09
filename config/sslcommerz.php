<?php

// config for Zobay/LaravelSslCommerz
return [
    'sandbox'          => env('SSLCOMMERZ_SANDBOX', true),
    'sandbox_base_url' => 'https://sandbox.sslcommerz.com',
    'live_base_url'    => 'https://securepay.sslcommerz.com',
    'version'          => 'v4',
    'validation_version' => 1,
    'paths' => [
        'init'       => '/gwprocess/v4/api.php',
        'validation' => '/validator/api/validationserverAPI.php',
    ],
    'credentials' => [
        'store_id'     => env('SSLCOMMERZ_STORE_ID'),
        'store_passwd' => env('SSLCOMMERZ_STORE_PASSWD'),
    ],
];

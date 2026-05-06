<?php

// config for Zobay/LaravelSslCommerz
return [
    'sandbox'          => env('SSLCOMMERZ_SANDBOX', true),
    'sandbox_base_url' => 'https://sandbox.sslcommerz.com',
    'live_base_url'    => 'https://securepay.sslcommerz.com',
    'version'          => 'v4',
    'credentials' => [
        'store_id'     => env('SSLCOMMERZ_STORE_ID'),
        'store_passwd' => env('SSLCOMMERZ_STORE_PASSWD'),
    ],
    'success_url' => env('SSLCOMMERZ_SUCCESS_URL'),
    'fail_url'    => env('SSLCOMMERZ_FAIL_URL'),
    'cancel_url'  => env('SSLCOMMERZ_CANCEL_URL'),
    'ipn_url'     => env('SSLCOMMERZ_IPN_URL'),
];

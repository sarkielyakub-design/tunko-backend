<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Reloadly Environment
    |--------------------------------------------------------------------------
    */

    'environment' => env('RELOADLY_ENV', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | OAuth
    |--------------------------------------------------------------------------
    */

    'client_id' => env('RELOADLY_CLIENT_ID'),

    'client_secret' => env('RELOADLY_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    */

    'auth_url' => env(
        'RELOADLY_AUTH_URL',
        'https://auth.reloadly.com'
    ),

    'topup_url' => env(
        'RELOADLY_TOPUP_URL',
        'https://topups.reloadly.com'
    ),

    'giftcard_url' => env(
        'RELOADLY_GIFTCARD_URL',
        'https://giftcards.reloadly.com'
    ),

    /*
    |--------------------------------------------------------------------------
    | Timeouts
    |--------------------------------------------------------------------------
    */

    'timeout' => env(
        'RELOADLY_TIMEOUT',
        30
    ),

];
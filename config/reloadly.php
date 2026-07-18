<?php

return [

    'environment' => env('RELOADLY_ENV', 'sandbox'),

    'client_id' => env('RELOADLY_CLIENT_ID'),

    'client_secret' => env('RELOADLY_CLIENT_SECRET'),

    'audience' => env(
        'RELOADLY_AUDIENCE',
        'https://topups.reloadly.com'
    ),

    'auth_url' => env(
        'RELOADLY_AUTH_URL',
        'https://auth.reloadly.com'
    ),

    'topup_url' => env(
        'RELOADLY_TOPUP_URL',
        'https://topups.reloadly.com'
    ),

    'timeout' => 30,

];
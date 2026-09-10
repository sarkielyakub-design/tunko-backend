<?php

return [

    'environment' => env('THUNES_ENV', 'sandbox'),

    'base_url' => env('THUNES_BASE_URL'),

    'api_key' => env('THUNES_API_KEY'),

    'api_secret' => env('THUNES_API_SECRET'),

    'webhook_secret' => env('THUNES_WEBHOOK_SECRET'),

    'timeout' => env('THUNES_TIMEOUT', 30),

    'retry' => env('THUNES_RETRY', 3),

];
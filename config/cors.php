<?php

return [

    'paths' => [
        'api/*',
        'sanctum/csrf-cookie',
    ],

    'allowed_methods' => ['*'],

    'allowed_origins' => [

        // Local Development
        'http://localhost:3000',
        'http://127.0.0.1:3000',

        // Production Website
        'https://tunkomoney.com',
        'https://www.tunkomoney.com',

        // Admin
        'https://admin.tunkomoney.com',

        // API
        'https://api.tunkomoney.com',

        // Optional Vercel Preview
        'https://tunkowebsite-pr2h-git-main-sarkielyakub-designs-projects.vercel.app',

    ],

    'allowed_origins_patterns' => [
        'https://*.vercel.app',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
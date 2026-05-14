<?php

return [
    'paths' => ['api/*', 'oauth/*', 'login', 'register', 'forgot_password', 'reset_password'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'http://127.0.0.1:3000', // if React frontend local
        'http://localhost:3000',
        'https://ticketsystem.encantotech.in', // frontend
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Authorization'],

    'max_age' => 0,

    'supports_credentials' => true,
];



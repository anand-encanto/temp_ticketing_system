<?php

$allowedOrigins = array_values(array_unique(array_filter(array_map(
    static fn ($origin) => rtrim(trim($origin), '/'),
    explode(',', env('CORS_ALLOWED_ORIGINS', implode(',', [
        env('FRONTEND_URL', 'https://support.mcdonalds.mu'),
        'https://ticketsystem.encantotech.in',
        'http://localhost:3000',
        'http://127.0.0.1:3000',
        'http://localhost:5173',
        'http://127.0.0.1:5173',
    ])))
))));

return [
    'paths' => ['api/*', 'oauth/*', 'login', 'register', 'forgot_password', 'reset_password'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $allowedOrigins,

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => ['Authorization'],

    'max_age' => 0,

    'supports_credentials' => true,
];


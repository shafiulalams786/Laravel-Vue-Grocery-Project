<?php

return [

    /*
    |--------------------------------------------------------------------------
    | CORS Configuration
    |--------------------------------------------------------------------------
    |
    | Allows Vue 3 frontend (port 5173) to call the Laravel API (port 8000).
    | For production, replace 'allowed_origins' with your actual domain.
    |
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'up'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        env('FRONTEND_URL', 'http://localhost:5173'),
        'http://localhost:3000',
        'http://localhost:8000',
        'http://127.0.0.1:5173',
    ],

    'allowed_origins_patterns' => [],

    // Must include Authorization for Sanctum Bearer token
    'allowed_headers' => [
        'Content-Type',
        'Accept',
        'Authorization',
        'X-Requested-With',
        'X-CSRF-TOKEN',
        'X-XSRF-TOKEN',
        'Origin',
    ],

    'exposed_headers' => [],

    'max_age' => 3600,

    // true only if you use cookies/sessions (we use Bearer tokens so false is fine)
    'supports_credentials' => false,

];

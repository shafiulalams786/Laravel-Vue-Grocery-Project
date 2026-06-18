<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sanctum Configuration
    |--------------------------------------------------------------------------
    |
    | This app uses STATELESS token-based auth (Bearer tokens stored in
    | localStorage on the frontend). Cookies and CSRF are NOT used.
    |
    */

    // Stateful domains — not needed for pure token auth, kept for completeness
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', implode(',', [
        'localhost',
        'localhost:5173',
        '127.0.0.1',
        '127.0.0.1:8000',
    ]))),

    'guard' => ['web'],

    // null = tokens never expire. Set to minutes e.g. 10080 = 7 days
    'expiration' => null,

    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),

    'middleware' => [
        'authenticate_session' => Laravel\Sanctum\Http\Middleware\AuthenticateSession::class,
        'encrypt_cookies'      => Illuminate\Cookie\Middleware\EncryptCookies::class,
        'validate_csrf_token'  => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    ],

];

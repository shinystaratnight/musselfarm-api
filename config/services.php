<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'stripe' => [
        'stripe_key' => env('STRIPE_KEY'),
        'stripe_secret' => env('STRIPE_SECRET'),
        'stripe_trial' => env('STRIPE_TRIAL'),
        'stripe_hook_secret' => env('STRIPE_WEBHOOK_SECRET')
    ],

    'api' => [
        'api_url' => env('API_URL'),
        'front_end_url' => env('FRONT_END_URL')
    ],

    'passport' => [
        'token_expire_in' => env('PASSPORT_TOKEN_EXPIRE_IN', 15),
        'refresh_token_expire_in' => env('PASSPORT_REFRESH_TOKEN_EXPIRE_IN', 1)
    ]
];

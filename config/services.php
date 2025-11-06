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

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'flutterwave' => [
        'public'   => env('FLW_PUBLIC_KEY'),
        'secret'   => env('FLW_SECRET_KEY'),
        'encrypt'  => env('FLW_ENCRYPTION_KEY'),
        'base_url' => env('FLW_BASE_URL', 'https://api.flutterwave.com/v3'),
        'webhook'  => [
            'hash' => env('FLW_WEBHOOK_HASH'),
        ],
        'fallback_ngn_usd'   => env('FLW_FALLBACK_NGN_USD', 0.0010),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
    ],

];

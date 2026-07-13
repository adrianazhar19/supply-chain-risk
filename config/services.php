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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
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

    'worldbank' => [
        'url' => env('WORLD_BANK_URL', 'https://api.worldbank.org/v2'),
    ],

    'newsapi' => [
        'key' => env('NEWS_API_KEY'),
    ],

    'gnews' => [
        'url' => env('GNEWS_URL', 'https://gnews.io/api/v4'),
    ],

    'openmeteo' => [
        'url' => env('OPEN_METEO_URL', 'https://api.open-meteo.com/v1'),
    ],

    'exchangerate' => [
        'url' => env('EXCHANGE_RATE_URL', 'https://api.exchangerate-api.com/v4'),
    ],

];

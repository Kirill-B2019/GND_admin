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

    'gnd' => [
        'node_url' => env('GND_NODE_URL', 'http://localhost:8182'),
        'api_key' => env('GND_API_KEY'),
        'admin_secret' => env('GND_ADMIN_SECRET'),
        'gndself_address' => env('GND_GNDSELF_ADDRESS', ''),
        // Состояние ГАНИМЕД GENESIS (GND) для обзора в админке
        'genesis' => [
            'total_supply' => env('GND_GENESIS_TOTAL_SUPPLY', '1000000000000000000000000000'),
            'circulating_supply' => env('GND_GENESIS_CIRCULATING_SUPPLY', '100000000000000000000000000'),
            'decimals' => (int) env('GND_GENESIS_DECIMALS', 18),
        ],
    ],

];

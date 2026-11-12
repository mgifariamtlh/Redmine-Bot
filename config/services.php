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

    'redmine' => [
        // Nama Custom Field issue di Redmine yang berisi jam deploy.
        'deploy_time_field' => env('REDMINE_DEPLOY_TIME_FIELD', 'Jam Deploy'),
        // Batas waktu tiap request ke Redmine (detik) dan jumlah issue terbaru per status.
        'timeout'           => env('REDMINE_TIMEOUT', 60),
        'limit_per_status'  => env('REDMINE_LIMIT_PER_STATUS', 50),
    ],

];

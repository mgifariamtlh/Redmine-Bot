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
        'deploy_time_field'    => env('REDMINE_DEPLOY_TIME_FIELD', 'Jam Deploy'),
        // Nama Custom Field tanggal deploy. ID-nya (angka) otomatis dicari; isi
        // REDMINE_DEPLOY_DATE_FIELD_ID bila pencarian otomatis gagal.
        'deploy_date_field'    => env('REDMINE_DEPLOY_DATE_FIELD', 'Tanggal Deploy'),
        'deploy_date_field_id' => env('REDMINE_DEPLOY_DATE_FIELD_ID'),
        // Hanya ambil issue dengan Tanggal Deploy >= N bulan lalu.
        'deploy_months_back'   => env('REDMINE_DEPLOY_MONTHS_BACK', 3),
        // Batas waktu tiap request ke Redmine (detik) dan jumlah maksimum issue per sync.
        'timeout'              => env('REDMINE_TIMEOUT', 60),
        'limit'              => env('REDMINE_LIMIT', 100),
    ],

];

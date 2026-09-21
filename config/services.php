<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'whatsapp' => [
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN', 'marell_demo_token_2026'),
        'provider'     => env('WHATSAPP_PROVIDER', 'meta'),
        'token'        => env('WHATSAPP_TOKEN'),
        'phone_id'     => env('WHATSAPP_PHONE_ID'),
    ],

    'sms' => [
        'director_phone' => env('DIRECTOR_PHONE', '254700000001'),
    ],

    'africastalking' => [
        'username' => env('AFRICASTALKING_USERNAME'),
        'api_key'  => env('AFRICASTALKING_API_KEY'),
        'sender'   => env('AFRICASTALKING_SENDER', 'MARELL'),
    ],

    'mpesa' => [
        'env'             => env('MPESA_ENV', 'sandbox'),
        'consumer_key'    => env('MPESA_CONSUMER_KEY'),
        'consumer_secret' => env('MPESA_CONSUMER_SECRET'),
        'shortcode'       => env('MPESA_SHORTCODE'),
        'passkey'         => env('MPESA_PASSKEY'),
        'callback_url'    => env('MPESA_CALLBACK_URL'),
    ],

];

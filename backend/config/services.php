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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'getnet' => [
        'environment' => env('GETNET_ENVIRONMENT', 'sandbox'),
        'seller_id' => env('GETNET_SELLER_ID'),
        'client_id' => env('GETNET_CLIENT_ID'),
        'client_secret' => env('GETNET_CLIENT_SECRET'),
    ],

    'asaas' => [
        'environment' => env('ASAAS_ENVIRONMENT', 'sandbox'),
        'api_key' => env('ASAAS_API_KEY'),
        'base_url' => env('ASAAS_ENVIRONMENT', 'sandbox') === 'production' 
            ? 'https://www.asaas.com/api/v3' 
            : 'https://sandbox.asaas.com/api/v3',
    ],

];

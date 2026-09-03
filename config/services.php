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

    // Was used with SQL Server's EncryptByPassPhrase for the partner
    // forgot-password reset flow (PortalApp.Classes.Constants.passphrase in
    // the old app). MySQL has no EncryptByPassPhrase equivalent, and the
    // "forgotPasswordUpdate" procedure that flow depends on wasn't part of
    // the SQL export this migration was given, so the whole reset flow is
    // non-functional right now regardless of this value - see
    // MIGRATION_NOTES.md. Left in place, unused, in case a future decision
    // revives that flow.
    'user_reset' => [
        'passphrase' => env('USER_RESET_PASSPHRASE'),
    ],

];

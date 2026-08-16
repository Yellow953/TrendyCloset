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

    /*
    |--------------------------------------------------------------------------
    | Marketing analytics
    |--------------------------------------------------------------------------
    |
    | Both are fed by App\Support\Tracking. An empty id renders that destination
    | not at all — no script, no beacon — so local and staging cost a visitor
    | nothing and never pollute a live property. Set these in production only.
    |
    | Meta: verifying the wiring is done from Events Manager → Test Events, which
    | pairs with the browser session; there is no test-event code to set here.
    | That is a Conversions API concern, and the site sends nothing server-side.
    |
    | Google: this is a GA4 measurement id (G-XXXXXXXXXX), loaded through gtag.js
    | directly rather than a Tag Manager container — there is no GTM container to
    | administer, and the events are already described server-side.
    |
    */

    'meta' => [
        'pixel_id' => env('META_PIXEL_ID'),
    ],

    'google' => [
        'ga4_id' => env('GA4_MEASUREMENT_ID'),
    ],

];

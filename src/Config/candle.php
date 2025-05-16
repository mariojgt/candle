<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    |
    | This is the URI prefix where Candle will be accessible from.
    |
    */
    'route_prefix' => 'api/analytics/events',

    /*
    |--------------------------------------------------------------------------
    | Dashboard Route
    |--------------------------------------------------------------------------
    |
    | This is the URI where the Candle dashboard will be accessible from.
    |
    */
    'dashboard_route' => 'analytics/dashboard',

    /*
    |--------------------------------------------------------------------------
    | JavaScript Tracker Path
    |--------------------------------------------------------------------------
    |
    | This is the URI where the Candle JavaScript tracker will be accessible from.
    |
    */
    'tracker_path' => 'analytics/tracker.js',

    /*
    |--------------------------------------------------------------------------
    | Data Retention
    |--------------------------------------------------------------------------
    |
    | Number of days to retain analytics data. Set to null for indefinite retention.
    |
    */
    'data_retention' => 365,

    /*
    |--------------------------------------------------------------------------
    | Default Tracking Options
    |--------------------------------------------------------------------------
    |
    | Default settings for the JavaScript tracker.
    |
    */
    'tracking' => [
        'exclude_bots' => true,
        'anonymize_ips' => true,
        'track_clicks' => true,
        'track_forms' => true,
        'cookie_timeout' => 30, // days
    ],

    /*
    |--------------------------------------------------------------------------
    | API Key Settings
    |--------------------------------------------------------------------------
    |
    | Settings for the API key management.
    |
    */
    'api_keys' => [
        'auto_expire' => 90, // days without usage before auto-expiration, null for never
        'expire_notification' => true, // send notification before key expiration
    ],

    /*
    |--------------------------------------------------------------------------
    | Dashboard Authentication
    |--------------------------------------------------------------------------
    |
    | If true, the dashboard requires authentication using Laravel's built-in
    | authentication system. If false, you'll need to implement your own auth.
    |
    */
    'use_default_auth' => true,

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Middleware to be applied to the dashboard routes.
    |
    */
    'middleware' => ['web', 'auth', \Mariojgt\Candle\Http\Middleware\VerifySiteOwnership::class],

    /*
    |--------------------------------------------------------------------------
    | Magic Link Settings
    |--------------------------------------------------------------------------
    |
    | Settings for magic link authentication.
    |
    */
    'magic_links' => [
        'enabled' => true,
        'expires_in' => 15, // minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Multi-tenant Settings
    |--------------------------------------------------------------------------
    |
    | Settings for multi-tenant functionality.
    |
    */
    'multi_tenant' => [
        'enabled' => true,
        'enforce_ownership' => true, // Ensure users can only access their own sites
    ],
];

<?php

return [

    'tracking' => [

        /*
         * Beacons accepted per minute, per IP, on the public collector. A browsing visitor
         * sends a few; this ceiling stops a script filling the events table.
         */
        'rate_limit' => env('MARKETING_TRACKING_RATE_LIMIT', 120),

        /*
         * How long raw events are kept. Reports read sessions and visitors, so events can be
         * pruned without losing the funnel — but a journey timeline only goes back this far.
         * Event volume is the largest table in this module, so this is the control that keeps
         * it in hand. Pruning is not scheduled yet; see docs when it is.
         */
        'event_retention_days' => env('MARKETING_EVENT_RETENTION_DAYS', 400),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google Ads (reporting, read-only)
    |--------------------------------------------------------------------------
    | App-level credentials live here (server environment only). The refresh
    | token is obtained through the Connect button on the Integrations page and
    | stored encrypted in marketing_ad_connections — never in the environment.
    */
    'google_ads' => [
        // Google Ads manager account → Admin → API Center.
        'developer_token' => env('GOOGLE_ADS_DEVELOPER_TOKEN'),

        // Google Cloud → Credentials → OAuth client (Web application).
        'client_id' => env('GOOGLE_ADS_CLIENT_ID'),
        'client_secret' => env('GOOGLE_ADS_CLIENT_SECRET'),

        // Must match an "Authorized redirect URI" on the OAuth client exactly.
        // Null uses the app's own callback route; set it when the app sits behind
        // a proxy that makes Laravel generate the wrong scheme or host.
        'redirect_uri' => env('GOOGLE_ADS_REDIRECT_URI'),

        // Google retires each version about a year after release; bump when notified.
        'api_version' => env('GOOGLE_ADS_API_VERSION', 'v25'),

        // How far back an account's history goes: this many days before it was first
        // connected. Older data is never pulled.
        'history_days' => (int) env('GOOGLE_ADS_HISTORY_DAYS', 90),

        // Trailing window re-pulled on every later run: Google keeps restating
        // conversions for clicks up to 30 days old.
        'refresh_days' => (int) env('GOOGLE_ADS_REFRESH_DAYS', 30),

        'timeout' => (int) env('GOOGLE_ADS_TIMEOUT', 60),
    ],

    /*
    |--------------------------------------------------------------------------
    | Meta Ads (reporting, read-only)
    |--------------------------------------------------------------------------
    | Connected with a Business Manager system user token, pasted once on the
    | Integrations page and stored encrypted in marketing_ad_connections. No
    | OAuth, so no app review is needed. Nothing here is required to connect.
    */
    'meta_ads' => [
        // Meta for Developers → the app → App settings → Basic. Optional: when set, every
        // call is signed with appsecret_proof, which Meta requires once "Require app secret"
        // is switched on for the app.
        'app_secret' => env('META_ADS_APP_SECRET'),

        // Meta retires each version about two years after release; bump when notified.
        'api_version' => env('META_ADS_API_VERSION', 'v24.0'),

        // How far back an account's history goes: this many days before it was first
        // connected. Older data is never pulled.
        'history_days' => (int) env('META_ADS_HISTORY_DAYS', 90),

        // Trailing window re-pulled on every later run: Meta keeps restating conversions
        // for up to 28 days after the click (its longest attribution window).
        'refresh_days' => (int) env('META_ADS_REFRESH_DAYS', 28),

        // Which Meta action types count as a "conversion". Meta has no single conversion
        // metric; "lead" is its all-leads total (pixel leads + on-Facebook lead forms).
        // Comma-separated, e.g. "lead,schedule_total". Do not list a total and its parts.
        'conversion_actions' => array_values(array_filter(array_map('trim', explode(',', (string) env('META_ADS_CONVERSION_ACTIONS', 'lead'))))),

        'timeout' => (int) env('META_ADS_TIMEOUT', 60),
    ],

];

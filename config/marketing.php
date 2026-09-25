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

];

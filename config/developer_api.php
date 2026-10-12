<?php

/*
| Developer API — read-only keys, one location each, created by developers in the Developer Portal.
*/
return [

    // Every key starts with this, so a leaked key is easy to recognise in logs and scanners.
    'key_prefix' => 'dds_',

    // Requests per minute allowed for every new key (developers cannot change it).
    'default_rate_limit' => (int) env('DEVELOPER_API_RATE_LIMIT', 30),

    // The longest start..end window one bookings request may ask for.
    'max_range_days' => 93,

    'per_page' => 100,
    'max_per_page' => 500,

];

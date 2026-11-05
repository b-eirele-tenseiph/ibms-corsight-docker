<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Logs Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during creating activity logs for
    | various events.
    |
    */

    // Log names
    'auth'     => 'Authentication',
    'info'     => 'information',
    'warning'  => 'warning',
    'error'    => 'error',

    // Device-related logs
    'battery' => [
        'message' => 'Current device battery is :battery.',
    ],

    // Environment-related logs
    'temperature' => [
        'message' => 'Current room temperature is :temp℃',
    ],

    'co2' => [
        'message' => 'Current CO₂ level is :co2 PPM',
    ],

    'humidity' => [
        'message' => 'Current humidity level is :humidity%',
    ],

    'scene' => [
        'need_to_configure' => 'Device needs to be configured before usage',
        'failed_to_execute' => 'Failed to execute scene command',
    ]
];

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Supported Client Timezones
    |--------------------------------------------------------------------------
    |
    | You can list all timezones your app allows the client to use.
    |
    */

    'supported' => [
        'Asia/Manila' => 'Philippine Time (PHT)',
        'Asia/Tokyo'  => 'Japan Standard Time (JST)',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Client Timezone
    |--------------------------------------------------------------------------
    |
    | This timezone will be used if the client hasn't provided one,
    | or if it's not in the supported list.
    |
    */

    'default' => env('LOCAL_TIMEZONE', 'Asia/Manila'),
];

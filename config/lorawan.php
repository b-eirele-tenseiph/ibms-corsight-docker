<?php

use App\Drivers\Commands\{KerlinkControls, ThingparkControls, MilesightControls};
use App\Drivers\Credentials\ThingparkToken;

return [

    /*
    |--------------------------------------------------------------------------
    | Maxim tries for Lorawan Commands
    |--------------------------------------------------------------------------
    |
    | This is where the maximum tries for commanding lorawan can be set.
    |
    */
    'tries' => env('LORAWAN_TRY_NUMBER', 0),

    /*
    |--------------------------------------------------------------------------
    | Lorawan Connections
    |--------------------------------------------------------------------------
    |
    | This is where all the lorawan connections are configured.
    | Each connection can have its own class and macro for token generation.
    |
    */

    'connections' => [
        'thingpark' => [
            'class' => ThingparkControls::class,
            'macro' => ThingparkToken::class,
            'token_name' => 'thingpark',
            'hostname' => env('THINGPARK_API_BASE_URL', 'https://thingparkenterprise.au.actility.com/thingpark/dx'),
            'oauth' => [
                'url' => env('THINGPARK_OAUTH_BASE_URL', 'https://thingparkenterprise.au.actility.com/thingpark/dx/admin/latest/api'),
                'grant_type' => 'client_credentials',
                'client' => [
                    'id' => env('THINGPARK_OAUTH_CLIENT_ID', null),
                    'secret' => env('THINGPARK_OAUTH_CLIENT_SECRET', null),
                ]
            ],
        ],
        'milesight' => [
            'class' => MilesightControls::class
        ],
        'kerlink' => [
            'class' => KerlinkControls::class
        ]

    ],

];

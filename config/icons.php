<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Device Icons
    |--------------------------------------------------------------------------
    |
    | Define the icons used for various devices in the system.
    |
    */

    'device_icons' => [
        'purifier' => 'material-symbols:air-purifier-outline',
        'humidifier' => 'mdi:air-humidifier',
        'fan' => 'mingcute:fan-line',
        'printer' => 'mdi:printer-outline',
        'paper shredder' => 'tabler:file-shredder',
        'coffee' => 'ic:outline-coffee-maker',
        'lock_on' => 'material-symbols:lock-outline',
        'lock_off' => 'material-symbols:lock-open-outline',
        'blender' => 'mdi:blender-outline',
        'microwave' => 'material-symbols:microwave-gen-outline',
        'kettle' => 'lucide-lab:kettle-electric',
        'bot' => 'material-symbols:switch-outline-rounded',
    ],

    'sensor_icons' => [
        'WS202' => [
            'motion-detected' => 'la:walking',
            'no-motion' => 'la:male'
        ],
        'R313A' => [
            'open' => 'lucide:door-closed',
            'closed' => 'lucide:door-open'
        ],
        'RB11E' => [
            'vacant' => 'ci:user-voice',
            'occupied' => 'ci:user-03'
        ]
    ]
];

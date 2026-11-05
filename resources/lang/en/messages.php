<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Messages
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for default exception message,
    | should there be an unforeseen error.
    |
    | 0xx -> Device Log
    | 1xx -> General Log
    | 2xx -> System Log
    */

    'default'  => [
        'error'        => [
            'message' => 'There was a problem processing your request.',
            'action'  => 'If the problem persists, please contact support.'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Messages
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for audit messages.
    | Both activity logs and notifications.
    |
    */
    'audit'  => [
        'A001'  => [
            'message' => '[:user] generated an electric report with file name [:filename].'
        ],
        'A002'  =>  [
            'message' => '[:user-admin] updated the account information.',
        ],
        'A003'  =>  [
            'message' => '[:super-admin] has generated a password reset link for [:user-admin] ',
        ],
        'A004'  => [
            'message' => 'A user has been created for room [:room].'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Information Messages
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for information messages.
    | Both activity logs and notifications.
    |
    */

    'information'  => [
        'I000'  => [
            'message' => 'The [:command] command was successfully executed for [:device].'
        ],
        'I001'  => [
            'message' => 'The [:command] command was successfully issued for [:device].'
        ],
        'I002'  => [
            'message' => 'The passcode issued for room [:room] has been deleted and is now invalid.'
        ],
        'I003' => [
            'message' => 'A new order (#:order) has been placed by :user.'
        ],
        'I004' => [
            'message' => 'Your order (#:order) is now being prepared.'
        ],
        'I005' => [
            'message' => 'Your order (#:order) is ready. Please enjoy your meal.',
        ],
        'I006' => [
            'message' => '[:room]: Scene [:scene] was successfully executed.'
        ],
        'I007' => [
            'message' => 'Scene [:scene] was successfully executed.'
        ],

        'I008'  => [
            'message' => 'A user has been created for room [:room].'
        ],
        'I009' => [
            'message' => 'Order #:order was cancelled by :user.'
        ],

        // General
        'I100'  => [
            'message' => '[:room]: User logged in.'
        ],
        'I101'  => [
            'message' => '[:room]: User logged out.'
        ],

        // System
        'I200'  => [
            'message' => 'Import of ":fileName" has been completed. :rows records were imported.'
        ],
        'I201'  => [
            'message' => 'Export of ":fileName" has been completed. :rows records were exported.'
        ],

    ],


    /*
    |--------------------------------------------------------------------------
    | Warning Messages
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for warning messages.
    | Currently includes sensor data for temperature.
    |
    */

    'warning'      => [
        'W001'  => [
            'message' => '[:room] An unusually high temperature has been recorded.'
        ],
        'W002'  => [
            'message' => '[:room] An unusually low temperature has been recorded.'
        ],
        'W003'  => [
            'message' => 'Attention: High CO2 level detected in [:room]. Consider improving ventilation'
        ],
        'W004'  => [
            'message' => 'Attention: Low CO2 level detected in [:room]. Consider improving ventilation'
        ],
        'W005'  => [
            'message' => 'Attention: Humidity level is high in [:room]. Consider adjusting ventilation'
        ],
        'W006'  => [
            'message' => 'Attention: Humidity level is low in [:room]. Consider adjusting ventilation'
        ],
        'W007'  => [
            'message' => '[:device] Battery level is now at :battery% in [:room]. The battery is approaching a low level.'
        ],
        'W008' => [
            'message' => '[:device] Battery is critical at :battery% in [:room]. Replace or charge immediately.'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Error Messages
    |--------------------------------------------------------------------------
    |
    | The following language lines are used for exception messages for known
    | errors currently being handled.
    |
    */

    'error'        => [
        // SwitchBot
        'E000' => [
            'message' => 'A problem has occurred while processing your command.',
            'action'  => 'If the problem persists, please contact support.',
        ],
        'E001' => [
            'message' => 'Failed to set up the webhook. Connection cannot be established.',
            'action'  => 'Please check if your SwitchBot Account token is valid.',
        ],
        'E002' => [
            'message' => 'Failed to set up the webhook. An internal error has been encountered.',
            'action'  => 'Please try again. If the problem persists, please submit a support ticket.',
        ],
        'E003'  => [
            'message' => 'Commanding of [:device] cannot proceed. There was a problem parsing device type.',
            'action'  => 'Please submit a support ticket for assistance.'
        ],
        'E004'  => [
            'message' => 'Commanding of [:device] cannot proceed. Hub is offline or disconnected.',
            'action'  => 'Please check if device is connected to a hub and if the hub is connected to the internet.'
        ],
        'E005' => [
            'message' => 'The command payload for [:device] is not supported.',
            'action'  => 'Please submit a support ticket for assistance.'
        ],
        'E006'  => [
            'message' => 'Commanding of [:device] cannot proceed due to unauthorized access.',
            'action'  => 'Please check if SwitchBot Account token is valid.',
        ],
        'E007' => [
            'message' => 'Command execution for [:device] failed due to an invalid API route.',
            'action'  => 'Please submit a support ticket for assistance.'
        ],
        'E008' => [
            'message' => 'Command execution for [:device] failed due to API connectivity issues.',
            'action'  => 'Please submit a support ticket for assistance.'
        ],
        'E009' => [
            'message' => 'Command execution for [:device] failed because the device was not found or the command format is invalid.',
            'action'  => 'Please verify that the device exists in your SwitchBot account and perform a device re-scan if necessary.'
        ],
        'E010' => [
            'message' => 'Command execution for [:device] failed because the device is offline.',
            'action'  => 'Please ensure that cloud services are enabled for your device and verify it is connected to a hub.'
        ],
        'E011'   => [
            'message' => 'An emergency alert has been activated in [:room] and requires immediate attention.',
            'action'  => 'Please respond to the room immediately.'
        ],

        // LoRaWAN
        'E012' => [
            'message' => 'Commanding of [:device] is unable proceed. Device isn`t registered.',
            'action'  => 'Please submit a support ticket.',
        ],
        'E013' => [
            'message' => 'Commanding of [:device] is unable proceed. API connection responded with unauthorized.',
            'action'  => 'Please submit a support ticket.',
        ],
        'E014' => [
            'message' => 'Commanding of [:device] is unable proceed. API connection cannot be established.',
            'action'  => 'Please submit a support ticket.',
        ],
        'E015' => [
            'message' => 'Unable to proceed. Device model isn`t supported yet.',
            'action'  => 'Please submit a support ticket.',
        ],
        'E016' => [
            'message' => 'Unable to proceed. Credentials are missing.',
            'action'  => 'Please submit a support ticket.',
        ],
        'E017' => [
            'message' => 'Unable to proceed. API Authentication failed.',
            'action'  => 'Please submit a support ticket.',
        ],
        'E018' => [
            'message' => 'Unable to proceed. URL not found.',
            'action'  => 'Please submit a support ticket.',
        ],
        'E019' => [
            'message' => 'Unable to proceed. Unable to connect to ThingPark API.',
            'action'  => 'Please submit a support ticket.',
        ],
        'E020' => [
            'message' => 'Commanding of [:device] is unable proceed. MQTT broker responded with unauthorized.',
            'action'  => 'Please submit a support ticket.',
        ],
        'E021' => [
            'message' => 'Commanding of [:device] is unable proceed. Unable to connect to MQTT broker.',
            'action'  => 'Please submit a support ticket.',
        ],
        'E022' => [
            'message' => 'Unable to process request. Please try again.',
            'action'  => 'If the problem persists, please contact support.',
        ],
        'E023'  => [
            'message' => 'Failed to get status of [:device]. Internal error has been encountered.',
            'action'  => 'Please try again. If problem persists, please submit a support ticket.',
        ],
        'E024' => [
            'message' => 'Critical :type imbalance detected on [:device]. Imbalance level is :percentage%. Immediate attention is required.',
            'action'  => 'Please investigate the load distribution on the electric meter.',
        ],

        // System
        'E200'  => [
            'message' => 'Import of ":fileName" encountered :failed errors and may not have been fully processed.'
        ],
        'E201'  => [
            'message' => 'Export of ":fileName" failed and could not be completed.'
        ],

        // Switchbot Account
        'E025'  => [
            'message' => 'Failed to synchronize devices. Connection cannot be established.',
            'action'  => 'Please check if SwitchBot Accounts\' token is valid.',
        ],
        'E026' => [
            'message' => 'Failed to synchronize SwitchBot account for room [:account]. Please check the account credentials.',
            'action'  => 'Please try again. If problem persists, please submit a support ticket.',
        ],
    ],

];

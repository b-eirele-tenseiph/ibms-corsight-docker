<?php

return [

    /*
    |--------------------------------------------------------------------------
    | iBMS Settings Configuration
    |--------------------------------------------------------------------------
    |
    | This is where additional settings are configured. Settings involved are
    | all within iBMS environment.
    */

    /**
     * Switchbot Remote Initial Data
     */

    // SwitchBot Remote Air Conditioner starting configuration
    'sb_ac_default' => [
        'temperature' => 26,
        'mode' => 2,
        'fanSpeed' => 1,
        'powerState' => 'off'
    ],

    // SwitchBot Remote TV starting configuration
    'sb_tv_default' => [
        'powerState' => 'off'
    ],

    // SwitchBot Remote Projector starting configuration
    'sb_projector_default' => [
        'powerState' => 'off'
    ],

    // Workplace sensor x SB AC remote
    'sb_ac_control_delay' => env('SB_CONTROL_DELAY', 60), //seconds

    /**
     * Lorawan Device Configuration
     */

    // Additional settings for Electric Meter
    'electric_meter' => [
        'phase_a' => 'Phase A',
        'phase_b' => 'Phase B',
        'phase_c' => 'Phase C',
    ],

    // Additional settings for Milesight IoT Controller (UC300) connected to modbus
    'iot_controller' => [
        'slave_id'    => null,
        'slave_model' => null
    ],

    // Additional settings for Milesight AI Sensor (VS121)
    'ai_sensor' => [
        'operation' => 'region_people_counter'
    ],

    // Additional settings for Milesight Switch (WS501)
    'switch1' => [
        'switch_1' => 'switch_1'
    ],

    // Additional settings for Milesight Switch (WS502)
    'switch2' => [
        'switch_1' => 'switch_1',
        'switch_2' => 'switch_2',
    ],

    // Additional settings for Milesight Switch (WS503)
    'switch3' => [
        'switch_1' => 'switch_1',
        'switch_2' => 'switch_2',
        'switch_3' => 'switch_3',
    ],

];

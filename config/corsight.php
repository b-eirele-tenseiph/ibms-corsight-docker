<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Retention
    |--------------------------------------------------------------------------
    |
    | Window sizes for the slim event diary (corsight_device_data), the
    | person sessions (corsight_person_session) and the stored face photos.
    | Enforced by the corsight:prune command (see routes/console.php).
    |
    */

    'retention' => [
        'diary_days' => env('CORSIGHT_DIARY_RETENTION_DAYS', 90),
        'session_days' => env('CORSIGHT_SESSION_RETENTION_DAYS', 365),
        'photo_days' => env('CORSIGHT_PHOTO_RETENTION_DAYS', 90),
    ],

    /*
    |--------------------------------------------------------------------------
    | Stale session sweeper
    |--------------------------------------------------------------------------
    |
    | Open (status = in) person sessions with no appearance event for this
    | many minutes are closed as abandoned by CorsightPersonService::sweepStale,
    | which the people read path runs on every list/counter load.
    | Must stay well above Fortify's periodic-update interval so that an
    | actively observed person is never swept.
    |
    */

    'stale_session_minutes' => env('CORSIGHT_STALE_SESSION_MINUTES', 30),

    /*
    |--------------------------------------------------------------------------
    | Device health staleness
    |--------------------------------------------------------------------------
    |
    | A device with no event of any kind (appearance or camera_status)
    | in the diary for this many minutes is shown as "inactive" on the
    | device list. Must stay well above Fortify's camera_status
    | reporting interval so a live camera is never flagged.
    |
    */

    'health_stale_minutes' => env('CORSIGHT_HEALTH_STALE_MINUTES', 30),

];

<?php

namespace App\Services\ActivityLogger;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;

class ActivityLoggerService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $info                (1.0) Log info-level activity to the database.
    // $error               (2.0) Log error-level activity to the database.
    // logActivity          (3.0) Log an activity with the given type and
    //                            properties.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> info
     * <Function> Log info-level activity to the database.
     *
     * @param array $data
     *
     * @return void
     */
    public static function info(array $data): void
    {
        $device = $data['device'];

        self::logActivity(Activity::TYPE_INFO, $data, [
            'Code'   => $data['code'],
            'Target' => $device->name,
            'Floor'  => optional($device->room->floor)->name,
            'Room'   => optional($device->room)->name,
        ]);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> error
     * <Function> Log error-level activity to the database.
     *
     * @param array $data
     *
     * @return void
     */
    public static function error(array $data): void
    {
        $device = $data['device'];

        self::logActivity(Activity::TYPE_ERROR, $data, [
            'Code'    => $data['code'],
            'Action'  => $data['action'] ?? null,
            'Dev EUI' => $device->dev_eui,
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> logActivity
     * <Function> Log an activity with the given type and properties.
     *
     * @param string $type
     * @param array $data
     * @param array $properties
     *
     * @return void
     */
    private static function logActivity(string $type, array $data, array $properties): void
    {
        activity()
            ->inLog($type)
            ->performedOn($data['device'])
            ->causedBy($data['causer'] ?? Auth::user())
            ->withProperties(array_filter($properties)) // Filters out null or empty values
            ->log($data['message']);
    }
}

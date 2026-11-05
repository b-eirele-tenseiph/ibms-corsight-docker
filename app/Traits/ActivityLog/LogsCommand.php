<?php

namespace App\Traits\ActivityLog;

use App\Models\Activity;

trait LogsCommand
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $type             (1.0) Save logs for command request be it success or
    //                         failure.
    // saveActivityLog   (2.0) The type of log to save. Information is the
    //                         default
    // withType          (3.0) Override default type if needed.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $type
     * <Function> The type of log to save. Information is the default
     *
     * @var string
     */
    protected $type = Activity::TYPE_INFO;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> saveActivityLog
     * <Function> Save logs for command request be it success or failure.
     *
     * @param  String      $code
     * @param  String      $message
     * @param  String|null $action
     * @return void
     */
    public function saveActivityLog(String $code, String $message, ?String $action = null)
    {
        // Generate props for logging command.
        $props = [
            'Code'   => $code,
            'Floor'  => optional($this->device->room->floor)->name ?? '-not-set-',
            'Room'   => $this->device->room->name,
        ];

        // Add action to properties if it exists.
        if (!is_null($action))
            $props['action'] = $action;

        // Create activity log
        activity()
            ->inLog($this->type)
            ->performedOn($this->device)
            ->withProperties($props)
            ->log($message);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> withType
     * <Function> Override default type if needed.
     *
     * @param  String $type
     * @return self
     */
    public function withType(String $type): self
    {
        $this->type = $type;

        return $this;
    }
}

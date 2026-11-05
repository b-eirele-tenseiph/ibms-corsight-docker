<?php

namespace App\Traits\ActivityLog;

use App\Models\Activity;

trait LogsAudit
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $type             (1.0) Save logs for command request be it success or
    //                         failure.
    // saveActivityLog   (2.0) The type of log to save. Information is the
    //                         default

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $type
     * <Function> The type of log to save. Information is the default
     *
     * @var string
     */
    protected $type = Activity::TYPE_AUDIT;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> saveActivityLog
     * <Function> Save logs for command request be it success or failure.
     *
     * @param array $params
     * @return void
     */
    public function saveActivityLog(?array $params): void
    {
        $props = [
            'code' => $params['code'] ?: '',
        ];

        activity()
            ->inLog($this->type)
            ->causedBy(auth()->user())
            ->performedOn($params['performedOn'] ?: '')
            ->withProperties(array_filter($props))
            ->log($params['message'] ?: '');
    }
}

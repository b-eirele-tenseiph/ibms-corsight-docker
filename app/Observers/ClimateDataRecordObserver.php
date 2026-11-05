<?php

namespace App\Observers;

use App\Events\Lorawan\Climate\ClimateDataUpdated;
use App\Models\ClimateDataRecord;

class ClimateDataRecordObserver
{
    /**************************************************************************/
    /* Processing Hierarchy */
    /**************************************************************************/
    // created        (1.0) Broadcasts the record after creation.
    // updated        (2.0) Broadcasts the record after update.
    // broadcast      (3.0) Broadcasts the record when its hour bucket is not in
    //                      the future.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> created
     * <Function> Broadcasts the record after creation.
     */
    public function created(ClimateDataRecord $record): void
    {
        $this->broadcast($record);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> updated
     * <Function> Broadcasts the record after update.
     */
    public function updated(ClimateDataRecord $record): void
    {
        $this->broadcast($record);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> broadcast
     * <Function> Broadcasts the record when its hour bucket is not in
     *            the future.
     */
    protected function broadcast(ClimateDataRecord $record): void
    {
        if ($record->date->isFuture()) {
            return;
        }

        ClimateDataUpdated::dispatch($record);
    }
}

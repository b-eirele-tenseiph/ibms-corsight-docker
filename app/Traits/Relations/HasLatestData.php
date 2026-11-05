<?php

namespace App\Traits\Relations;

use App\Models\DeviceLatestData;

trait HasLatestData
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // booted               (1.0) Delete related positions to model.
    // latestData           (2.0) Get the latest data associated with the model.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> booted
     * <Function> Delete related latest data to model.
     *
     * @return void
     */
    protected static function bootHasLatestData(): void
    {
        static::deleting(function ($model) {
            $model->latestData()->delete();
        });
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> latestData
     * <Function> Get the latest data associated with the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\morphOne
     */
    public function latestData(): mixed
    {
        return $this->morphOne(DeviceLatestData::class, 'device');
    }
}

<?php

namespace App\Traits\Relations;

use App\Models\DeviceGridPosition;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasGridStackPosition
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // booted               (1.0) Delete related positions to model.
    // positions            (2.0) Get the positions associated with the model.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> booted
     * <Function> Delete related positions to model.
     *
     * @return void
     */
    protected static function bootHasGridStackPosition(): void
    {
        static::deleting(function ($post) {
            // Delete all positions associated with the model
            foreach ($post->positions as $position) {
                $position->delete();
            }
        });
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> positions
     * <Function> Get the positions associated with the model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function positions(): MorphMany
    {
        return $this->morphMany(DeviceGridPosition::class, 'device');
    }
}

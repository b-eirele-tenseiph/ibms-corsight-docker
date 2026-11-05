<?php

namespace App\Traits\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait FloorFilterable
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // scopeOnFloor       (2.0) Filter query by floor identifier.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> scopeOnFloor
     * <Function> Filter query by floor identifier
     *
     * @param Builder $query
     * @param string|null $room
     *
     * @return Builder
     */
    public function scopeOnFloor(Builder $query, ?string $floor): Builder
    {
        return $query->when($floor, function ($query, $floor) {
            return $query->whereHas('floor', function ($q) use ($floor) {
                $q->where('name', unslug($floor));
            });
        });
    }
}

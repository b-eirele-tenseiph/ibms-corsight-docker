<?php

namespace App\Traits\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait RoomFilterable
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // scopeInRoom        (1.0) Filter query by room name.
    // scopeInRoomArea    (2.0) Filter query by room area identifier.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> scopeInRoom
     * <Function> Filter query by room name
     *
     * @param Builder $query
     * @param string|null $room
     *
     * @return Builder
     */
    public function scopeInRoom(Builder $query, ?string $room): Builder
    {
        return $query->when($room, function ($query, $room) {
            return $query->whereRelation(
                'room',
                'rooms.name',
                unslug($room)
            );
        });
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> scopeInRoomArea
     * <Function> Filter query by room area identifier
     *
     * @param Builder $query
     * @param string|null $room
     *
     * @return Builder
     */
    public function scopeInRoomArea(Builder $query, ?string $room): Builder
    {
        return $query->when($room, function ($query, $room) {
            return $query->whereRelation(
                'roomArea',
                'room_areas.name',
                unslug($room)
            );
        });
    }
}

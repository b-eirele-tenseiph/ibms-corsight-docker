<?php

namespace App\Http\Controllers\Admin\RoomArea;

use App\Http\Controllers\Controller;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomAreaController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // fetch        (1.0) Fetch room areas within room.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> fetch
     * <Function> Fetch room areas within room.
     *
     * @param  \App\Models\Room              $room
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetch(Room $room): JsonResponse
    {
        return response()->json($room->roomAreas);
    }
}

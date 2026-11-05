<?php

namespace App\Http\Controllers\Admin\RoomMonitoring;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Services\RoomMonitoring\RoomMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Response;

class RoomMonitoringController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // index                (1.0) Fetch the list of rooms to map the devices
    //                            that are available.
    // getByType            (2.0) Single polymorphic endpoint to retrieve any
    //                            room device type by `type` query parameter.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> index
     * <Function> Fetch the list of rooms to map the devices that are available.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function index(Request $request, RoomMonitoringService $service): Response
    {
        $roomsQuery = $service->getRoomsQuery($request->query('floor'));
        $filteredQuery = $service->getFilteredRoomsQuery($request->query('floor'), $request->query('room'));

        return inertia('admin/room-monitoring/Index', [
            'rooms'         => $roomsQuery->get(),
            'query'         => $request->query(),
            'filteredRooms' => $filteredQuery->get(),
            'floors'        => Floor::all(),
            'icon'          => config('icons'),
        ]);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> getByType
     * <Function> Retrieve room devices by type (switches, thermostat, device,
     *            curtains, blindtilt, aircon, projector, television).
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\RoomMonitoring\RoomMonitoringService $service
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getByType(Request $request, RoomMonitoringService $service): JsonResponse
    {
        $type = basename($request->path());

        $method = match ($type) {
            'switches'    => 'getRoomSwitches',
            'thermostat'  => 'getRoomThermostat',
            'devices'     => 'getRoomDevices',
            'curtains'    => 'getRoomCurtains',
            'blind-tilt'  => 'getRoomBlindTilt',
            'aircon'      => 'getRoomAircon',
            'projector'   => 'getRoomProjector',
            'television'  => 'getRoomTelevision',
            default       => abort(422, 'Invalid device type'),
        };

        $room = $service->forRoom($request->query('room'))
            ->inArea($request->query('area'))
            ->{$method}();

        return response()->json($room);
    }
}

<?php

namespace App\Http\Controllers\Admin\ClimateAnalytics;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\RoomArea;
use App\Services\Admin\ClimateAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class ClimateDataController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy */
    /**************************************************************************/
    // __construct                 (1.0) Initialize the controller with the
    //                                   ClimateAnalyticsService dependency.
    // index                       (2.0) Render the climate analytics index page.
    // getClimateStatus            (3.0) Return ON & OFF pattern chart data.
    // getClimateFanSpeed          (4.0) Return fan speed chart data.
    // getClimateTemperature       (5.0) Return temperature chart data.
    // getClimateSetpoint          (6.0) Return setpoint chart data.
    // getClimateActualAndSetPoint (7.0) Return merged setpoint and actual
    //                                   chart data.
    // getClimateReadings          (8.0) Return the thermostat readings used by
    //                                   the stat cards.
    // getClimateData              (9.0) Dispatch the matching service method
    //                                   with the request filters.
    // resolveTimeFilter          (10.0) Resolve the requested window filter.
    // resolveFilters             (11.0) Build the filter payload shared by
    //                                   the page and the chart endpoints.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Initialize the controller with the ClimateAnalyticsService
     *            dependency.
     */
    public function __construct(
        protected ClimateAnalyticsService $service
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Render the climate analytics index page.
     */
    public function index(Request $request): Response
    {
        $rooms = Room::whereHas('lorawanDevices', fn($q) => $q->deviceType('thermostat'))
            ->get()
            ->each(function ($room) {
                $thermostats = $room->lorawanDevices()
                    ->deviceType('thermostat')
                    ->get(['id', 'room_area_id', 'updated_at']);

                $lastUplinkByArea = $thermostats
                    ->groupBy('room_area_id')
                    ->map(fn($devices) => $devices->max('updated_at'));

                $areas = RoomArea::whereIn(
                    'id',
                    $thermostats->pluck('room_area_id')->unique()->filter()
                )->get();

                $areas->each(fn(RoomArea $area) => $area->setAttribute(
                    'last_uplink',
                    $lastUplinkByArea[$area->id] ?? null
                ));

                $room->setRelation('roomAreas', $areas);
            });

        return inertia('admin/climate-analytics/Index', [
            'filters' => (object) $this->resolveFilters($request),
        ])->with(compact('rooms'));
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> getClimateStatus
     * <Function> Return ON & OFF pattern chart data.
     */
    public function getClimateStatus(Request $request): JsonResponse
    {
        return $this->getClimateData($request, 'getONandOFFPatterns');
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> getClimateFanSpeed
     * <Function> Return fan speed chart data.
     */
    public function getClimateFanSpeed(Request $request): JsonResponse
    {
        return $this->getClimateData($request, 'getFanSpeed');
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> getClimateTemperature
     * <Function> Return temperature chart data.
     */
    public function getClimateTemperature(Request $request): JsonResponse
    {
        return $this->getClimateData($request, 'getTemperature');
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> getClimateSetpoint
     * <Function> Return setpoint chart data.
     */
    public function getClimateSetpoint(Request $request): JsonResponse
    {
        return $this->getClimateData($request, 'getSetpoint');
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> getClimateActualAndSetPoint
     * <Function> Return merged setpoint and actual chart data.
     */
    public function getClimateActualAndSetPoint(Request $request): JsonResponse
    {
        return $this->getClimateData($request, 'getSetpointAndActual');
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> getClimateReadings
     * <Function> Return the thermostat readings used by the stat cards.
     */
    public function getClimateReadings(Request $request): JsonResponse
    {
        return response()->json([
            'readings' => $this->service->getReadings(
                $this->resolveTimeFilter($request),
                (string) $request->input('room', ''),
                (string) $request->input('room_area', ''),
            ),
        ]);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> getClimateData
     * <Function> Dispatch the matching service method with the request
     *            filters.
     */
    private function getClimateData(Request $request, string $method): JsonResponse
    {
        return response()->json(
            $this->service->$method(
                $this->resolveTimeFilter($request),
                (string) $request->input('room', ''),
                (string) $request->input('room_area', ''),
            )
        );
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> resolveTimeFilter
     * <Function> Resolve the requested window filter.
     *
     * @return array<string, string>|int
     */
    private function resolveTimeFilter(Request $request): array|int
    {
        $start = $request->input('start');
        $end = $request->input('end');

        if ($start && $end) {
            return ['start' => (string) $start, 'end' => (string) $end];
        }

        return (int) ($request->input('time') ?: ClimateAnalyticsService::DEFAULT_HOURS);
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> resolveFilters
     * <Function> Build the filter payload shared by the page and the chart
     *            endpoints.
     *
     * @return array<string, mixed>
     */
    private function resolveFilters(Request $request): array
    {
        $time = $this->resolveTimeFilter($request);

        return array_merge($request->query(), [
            'time' => is_array($time) ? null : $time,
            'start' => is_array($time) ? $time['start'] : null,
            'end' => is_array($time) ? $time['end'] : null,
            'resolution' => $this->service->resolutionFor($time),
        ]);
    }
}

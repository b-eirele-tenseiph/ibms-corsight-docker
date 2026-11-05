<?php

namespace App\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Http\{Request, JsonResponse};
use App\Services\MonitoringAndControls\MonitoringAndControlService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct              (1.0)  Constructor to initialize the
    //                                 AdminDashboardService.
    // index                    (2.0)  Display the admin dashboard page.
    // getRoom                  (3.0)  Retrieve the specific room from storage.
    // electricMeters           (4.0)  Get electric meter data either all or
    //                                 paginated.
    // deviceCountData          (5.0)  Load total device count.
    // roomsData                (6.0)  Load room data.
    // deviceSummaryData        (7.0)  Load device summary data.
    // electricConsumptionData  (8.0)  Load electric consumption overview data.
    // environmentSensorData    (9.0)  Load environment sensor data.
    // hourlyEnergyData         (10.0) Load hourly energy data for dashboard.
    // monthlyEnergyData        (11.0) Load monthly energy consumption data.
    // dailyEnergyData          (12.0) Load daily energy data and monthly totals.
    // deviceStatus             (13.0) Load device status.
    // recentActivity           (14.0) Load recent activity.
    // getClimate               (15.0) Get temperature monitor for each
    //                                 room per area.
    // deviceEnergyData         (16.0) Load electric meter data per meter.
    // carbonFootprintData      (17.0) Load carbon footprint of all meter.
    // powerFactorData          (18.0) Load power factor data per device.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Constructor to initialize the AdminDashboardService.
     *
     * @param \App\Services\Admin\AdminDashboardService $service
     */
    public function __construct(
        protected AdminDashboardService $service
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> index
     * <Function> Display the admin dashboard page.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Inertia\Response
     */
    public function index()
    {
        $locations = Room::with('lorawanDevices')
            ->select('id', 'name')
            ->get()
            ->filter(fn($room) => $room->lorawanDevices->contains(
                fn($device) => $device->classification === 'electric_meter'
            ))
            ->map(fn($room) => $room->only('id', 'name'))
            ->values();

        return inertia('admin/dashboard/Index', [
            'location_with_meters' => $locations,
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> getRoom
     * <Function> Retrieve the specific room from storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\MonitoringAndControls\MonitoringAndControlService $monitor
     * @param \App\Services\Admin\AdminDashboardService $admin
     * @return \Inertia\Response
     */
    public function getRoom(Request $request, MonitoringAndControlService $monitor, AdminDashboardService $admin): Response
    {
        $payload = $admin->loadRoomDevicesAndFilters($request, $monitor);

        return inertia('admin/dashboard-device/Index', array_merge([
            'room' => Room::select('id', 'name', 'number')->where('id', $request->room)->firstOrFail()
        ], $payload));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> electricMeters
     * <Function> Get electric meter data either all or paginated.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function electricMeters(Request $request): JsonResponse
    {
        $query = $this->service->loadElectricMeters($request);

        return response()->json(
            $request->boolean('all')
                ? $query->get(['id', 'name'])
                : $query->paginate(9)
        );
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> deviceCountData
     * <Function> Load total device count.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deviceCountData(): JsonResponse
    {
        return response()->json([
            'count' => $this->service->loadDeviceCount()
        ]);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> roomsData
     * <Function> Load room data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function roomsData(Request $request): JsonResponse
    {
        return response()->json([
            'rooms' => $this->service->loadRooms($request)
        ]);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> deviceSummaryData
     * <Function> Load device summary data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deviceSummaryData(): JsonResponse
    {
        return response()->json([
            'summary' => $this->service->loadDeviceSummary()
        ]);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> electricConsumptionData
     * <Function> Load electric consumption overview data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function electricConsumptionData(): JsonResponse
    {
        return response()->json([
            'consumption' => $this->service->loadElectricConsumptionOverview()
        ]);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> environmentSensorData
     * <Function> Load environment sensor data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function environmentSensorData(): JsonResponse
    {
        return response()->json([
            'sensors' => $this->service->loadEnvironmentSensorData()
        ]);
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> hourlyEnergyData
     * <Function> Load hourly energy data for dashboard.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function hourlyEnergyData(Request $request): JsonResponse
    {
        $device = $request->input('device', []);
        $isOverall = $request->input('overall', 'over_all');
        $hour      = $request->input('hour', 6);

        return response()->json([
            'hourly_consumption' => $this->service->loadEnergyHourlyData($device, $isOverall, $hour)
        ]);
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> monthlyEnergyData
     * <Function> Load monthly energy consumption data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function monthlyEnergyData(Request $request): JsonResponse
    {
        return response()->json([
            'monthly_consumption' => $this->service->loadEnergyMonthlyData($request->input('month', 6))
        ]);
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> dailyEnergyData
     * <Function> Load daily energy data and monthly totals.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function dailyEnergyData(Request $request): JsonResponse
    {
        $month = $request->input('month', 'current_month');

        return response()->json([
            'daily_cost' => $this->service->loadDailyEnergyCost($month),
            'cost_total'   => $this->service->loadMonthlyTotals()
        ]);
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> deviceStatus
     * <Function> Load device status.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deviceStatus(): JsonResponse
    {
        return response()->json([
            'status' => $this->service->loadDeviceStatus()
        ]);
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> recentActivity
     * <Function> Load recent activity.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function recentActivity(): JsonResponse
    {
        return response()->json([
            'recent_activity' => $this->service->loadRecentActivity()
        ]);
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> getACMonitor
     * <Function> Get temperature monitor for each room per area.
     *
     * @param \App\Models\Room $room
     * @param \App\Services\Admin\AdminDashboardService $service
     * @return \Illuminate\Http\JsonResponse
     */
    public function getClimate(Request $request, AdminDashboardService $service): JsonResponse
    {
        return response()->json($service->loadACMonitor($request->device, $request->filter));
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> deviceEnergyData
     * <Function> Load electric meter data per meter.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deviceEnergyData(Request $request): JsonResponse
    {
        return response()->json([
            'energy_data' => $this->service->loadElectricDataPerDevice($request->input('device.id')) ?? [],
        ]);
    }

    /**
     * <Layer number> (17.0)
     *
     * <Processing name> carbonFootprintData
     * <Function> Load carbon footprint of all meter.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function carbonFootprintData(Request $request): JsonResponse
    {
        return response()->json([
            'carbon' => $this->service->loadCarbonFootprint($request->query('device_ids')) ?? [],
        ]);
    }

    /**
     * <Layer number> (18.0)
     *
     * <Processing name> powerFactorData
     * <Function> Load power factor data per device.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function powerFactorData(Request $request): JsonResponse
    {
        return response()->json([
            'power_factor' => $this->service->loadPowerFactor($request->query('device_ids')) ?? collect(),
        ]);
    }

    /**
     * <Layer number> (19.0)
     *
     * <Processing name> visitorData
     * <Function> Load visitor counter and graph data.
     *
     */
    public function visitorData(): JsonResponse
    {
        return response()->json([
            'counter' => $this->service->loadVisitorCounter(),
            'graph'   => $this->service->loadVisitorGraphData()
        ]);
    }
}

<?php

namespace App\Services\Admin;

use App\Models\Activity;
use App\Models\Room;
use App\Models\{CorsightDevice, CorsightDeviceData, CorsightPersonSession};
use App\Models\{ElectricityRate, HourlyConsumption, HourlyVisitor, MonthlyConsumption};
use App\Models\{LorawanDevice, LorawanGateway};
use App\Models\{SwitchBotAccount, SwitchBotDevice, SwitchBotRemote};
use App\Services\MonitoringAndControls\MonitoringAndControlService;
use Carbon\{Carbon, CarbonPeriod};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // EMISSION_FACTOR           (1.0)  The emission factor for converting kWh
    //                                  to kg CO2.
    // loadElectricMeters        (2.0)  Load electric meters with search and
    //                                  filter for dashboard.
    // loadDeviceCount           (3.0)  Load device count summary for dashboard.
    // loadRooms                 (4.0)  Load rooms data for dashboard.
    // loadDeviceSummary         (5.0)  Load device summary for dashboard.
    // loadElectricConsumption   (6.0)  Load electric consumption overview for
    // Overview                         dashboard.
    // loadEnvironmentSensorData (7.0)  Load environment sensor data for
    //                                  dashboard.
    // loadDeviceStatus          (8.0)  Load device status for dashboard.
    // loadRecentActivity        (9.0)  Load recent activity logs.
    // loadDailyEnergyCost       (10.0) Load daily energy cost for dashboard.
    // loadMonthlyTotals         (11.0) Load monthly totals for dashboard.
    // loadEnergyHourlyData      (13.0) Load energy hourly data for dashboard.
    // loadEnergyMonthlyData     (14.0) Load energy monthly data for dashboard.
    // loadACMonitor             (15.0) Load Aircon temperature monitoring
    //                                  per room.
    // loadRoomDevicesAndFilters (16.0) Load room with devices based on selected
    //                                  tab and filters for dashboard.
    // loadElectricDataPerDevice (17.0) Load electric consumption data for a
    //                                  specific device.
    // loadCarbonFootprint       (18.0) Load carbon footprint data for given
    //                                  device IDs.
    // loadPowerFactor           (19.0) Load power factor data for given
    //                                  device IDs.
    // loadVisitorCounter        (20.0) Load visitor counter summary for
    //                                  dashboard.
    // loadVisitorGraphData      (21.0) Load visitor graph data for dashboard.
    // loadCorsightDashboardData (22.0) Load Corsight CCTV dashboard payload
    //                                  (recent logs, rooms, dwell time).
    // mapCorsightGender         (23.0) Map raw Fortify gender to dashboard
    //                                  enum.
    // mapCorsightAgeRange       (24.0) Re-bucket raw Fortify age group into
    //                                  dashboard ranges.
    // corsightRoomLabel         (25.0) Location label shared by rooms and
    //                                  recent logs.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> EMISSION_FACTOR
     * <Function> The emission factor for converting kWh to kg CO2.
     *
     * @var int
     */
    private const EMISSION_FACTOR = 0.7181;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> loadElectricMeters
     * <Function> Load electric meters with search and filter for dashboard.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function loadElectricMeters(Request $request): Builder
    {
        return LorawanDevice::select(['id', 'name', 'room_id'])
            ->deviceType('electricMeter')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                        ->orWhereHas('room', function ($roomQ) use ($search) {
                            $roomQ->where('name', 'LIKE', "%{$search}%");
                        });
                });
            })
            ->when($request->filled('location'), fn($q) => $q->where('room_id', $request->location));
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> loadDeviceCount
     * <Function> Load device count summary for dashboard.
     *
     * @return array
     */
    public function loadDeviceCount(): array
    {
        $accounts = SwitchBotAccount::selectRaw('
        COUNT(*) as total,
        COUNT(CASE WHEN last_synced_at IS NOT NULL THEN 1 END) as param
    ')->first();

        $gateways = ['total' => LorawanGateway::count()];

        $devices = [
            'total' => LorawanDevice::count()
                + SwitchBotDevice::count()
                + SwitchBotRemote::count(),
            'param' => 0
        ];

        $rooms = ['total' => Room::count(), 'param' => 0];

        return compact('accounts', 'gateways', 'devices', 'rooms');
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> loadRooms
     * <Function> Load rooms data for dashboard.
     *
     * @return array
     */
    public function loadRooms(Request $request): array
    {
        $query = Room::query()
            ->with([
                'floor:id,name',
                'lorawanDevices.latestData',
                'switchBotDevices.latestData',
                'switchBotRemotes.latestData',
            ])
            ->select(['id', 'name', 'floor_id']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhereHas('floor', fn($fq) => $fq->where('name', 'LIKE', "%{$search}%"));
            });
        }

        return $query
            ->get()
            ->map(function ($room) {
                $activeFromRemotes = $room->switchBotRemotes
                    ->filter(fn($d) => strtolower(data_get($d, 'latestData.context.powerState')) === 'on')
                    ->count();

                $activeFromDevices = $room->switchBotDevices
                    ->filter(function ($d) {
                        $ctx = (array) data_get($d, 'latestData.context', []);
                        $state = strtolower($ctx['powerState'] ?? $ctx['power'] ?? '');
                        return $state === 'on';
                    })->count();

                $activeFromLoRa = $room->lorawanDevices
                    ->filter(function ($d) {
                        return collect($d->latestData?->context ?? [])
                            ->values()
                            ->pluck('value')
                            ->intersect(['high', 'on'])
                            ->isNotEmpty();
                    })->count();

                $peopleCount = $room->lorawanDevices
                    ->where('model', 'VS121')
                    ->filter(fn($d) => data_get($d->latestData?->context, 'operation') === 'region_people_counter')
                    ->sum(fn($d) => (int) ($d->latestData?->context['people_counter']['value'] ?? 0));

                $occupied = $room->lorawanDevices
                    ->where('model', 'RB11E')
                    ->contains(fn($d) => !empty($d->latestData?->context['occupancy']['value']));

                return [
                    'id'         => $room->id,
                    'name'       => $room->name,
                    'floor'      => optional($room->floor)->name,
                    'active'     => $activeFromRemotes + $activeFromDevices + $activeFromLoRa,
                    'isOccupied' => $occupied,
                    'status'     => $occupied ? __('defaults.occupied') : __('defaults.vacant'),
                    'count'      => $peopleCount,
                    'slug'       => $room->slug,
                ];
            })
            ->all();
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> loadDeviceSummary
     * <Function> Load device summary for dashboard.
     *
     * @return array
     */
    public function loadDeviceSummary(): array
    {
        $remoteActive = fn($d) =>
        strtolower(data_get($d, 'latestData.context.powerState', '')) === 'on';

        $deviceActive = fn($d) =>
        strtolower(data_get($d, 'latestData.context.powerState', '')) === 'on' ||
            strtolower(data_get($d, 'latestData.context.power', '')) === 'on';

        $summary = [];

        $addData = function ($name, $devices, $isActive) use (&$summary) {
            $active = $devices->filter($isActive)->count();
            $total = $devices->count();

            $summary[] = [
                'name'       => $name,
                'active'     => $active,
                'total'      => $total,
                'percentage' => $total > 0 ? round(($active / $total) * 100) : 0,
            ];
        };

        $addData(
            __('defaults.switch'),
            LorawanDevice::with('latestData')->deviceType('switches')->get(),
            fn($device) => collect($device->latestData?->context ?? [])
                ->values()
                ->pluck('value')
                ->intersect(['high', 'on'])
                ->isNotEmpty()
        );

        $addData(
            __('defaults.aircon'),
            LorawanDevice::with('latestData')->deviceType('thermostat')->get()
                ->concat(
                    SwitchBotRemote::ofType('Air Conditioner')
                        ->with('latestData')
                        ->get()
                ),
            fn($d) => $d instanceof SwitchBotRemote
                ? $remoteActive($d)
                : strtolower(data_get($d, 'latestData.context.slave_power.value', '')) === 'on'
        );

        $addData(
            __('defaults.plug'),
            SwitchBotRemote::ofType('Plug', 'Plug Mini (JP)', 'Plug Mini (US)')
                ->with('latestData')
                ->get(),
            $deviceActive
        );

        $addData(
            __('defaults.bot'),
            SwitchBotRemote::ofType('Bot')
                ->with('latestData')
                ->get(),
            $deviceActive
        );

        return $summary;
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> loadElectricConsumptionOverview
     * <Function> Load electric consumption overview for dashboard.
     *
     * @return array
     */
    public function loadElectricConsumptionOverview(): array
    {
        return collect([now('Asia/Manila')->subMonths(2), now('Asia/Manila')->subMonth()])
            ->map(function ($month) {
                $start = $month->copy()->startOfMonth()->startOfDay()->utc();
                $end   = $month->copy()->endOfMonth()->endOfDay()->utc();
                $rate  = ElectricityRate::effectiveRate($month) ?? 0;

                $records = HourlyConsumption::select('date', 'consumption')
                    ->whereBetween('date', [$start->toDateTimeString(), $end->toDateTimeString()])
                    ->where('consumption->type', 'positive_active_energy')
                    ->get();

                $weeklyData = $records->groupBy(function ($record) {
                    return Carbon::parse($record->date)->setTimezone('Asia/Manila')->weekOfMonth;
                })->map(function ($weekRecords) use ($rate) {
                    return $weekRecords->sum(fn($record) => (float) $record->consumption['value'] * $rate);
                });

                return [
                    'name'  => $month->monthName,
                    'total' => round($weeklyData->sum(), 2),
                    'data'  => $weeklyData->sortKeys()->values()->map(fn($total, $key) => [
                        "Week " . ($key + 1),
                        round($total, 2),
                    ])->values(),
                ];
            })->toArray();
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> loadEnvironmentSensorData
     * <Function> Load environment sensor data for dashboard.
     *
     * @return array
     */
    public function loadEnvironmentSensorData(): array
    {
        $results = collect([
            'co2Data'        => ['series' => []],
            'humidityData'   => ['series' => []],
            'temperatureData' => ['series' => []],
            'illuminanceData' => ['series' => []],
        ])->toArray();

        $sensors = LorawanDevice::query()
            ->whereIn('model', ['RA0715', 'RB11E'])
            ->get()
            ->tap(fn($devices) => LorawanDevice::loadTopNData($devices, 10));

        $addSeries = function ($device, $type, &$results, $key) {
            $data = ($device->top_data ?? collect())->where('type', $type)
                ->sortBy('received_at')
                ->map(fn($d) => [$d->received_at, $d->context['value']])
                ->values();

            if ($data->isNotEmpty()) {
                $results[$key]['series'][] = [
                    'id'   => $device->id,
                    'name' => $device->name,
                    'data' => $data,
                ];
            }
        };

        // RA0715 devices -> CO2, Humidity, Temp
        $sensors->where('model', 'RA0715')->each(function ($device) use (&$results, $addSeries) {
            $addSeries($device, 'carbon_dioxide', $results, 'co2Data');
            $addSeries($device, 'humidity',        $results, 'humidityData');
            $addSeries($device, 'temperature',     $results, 'temperatureData');
        });

        // RB11E devices -> Illuminance, Temp
        $sensors->where('model', 'RB11E')->each(function ($device) use (&$results, $addSeries) {
            $addSeries($device, 'illuminance', $results, 'illuminanceData');
            $addSeries($device, 'temperature', $results, 'temperatureData');
        });

        return $results;
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> loadDeviceStatus
     * <Function> Load device status for dashboard.
     *
     * @return mixed
     */
    public function loadDeviceStatus(): mixed
    {
        $lorawanDevices = LorawanDevice::deviceType('switches')
            ->doesntHave('latestData')
            ->get()
            ->map(fn($d) => [
                'id'        => $d->id,
                'name'      => $d->name,
                'status' => __('defaults.no_connection'),
                'last_seen' => $d->created_at->timezone('Asia/Manila'),
                'icon'      => 'mdi:alert-circle-outline',
            ]);

        $switchbotDevicesNoData = SwitchBotDevice::query()
            ->doesntHave('latestData')
            ->get()
            ->map(fn($d) => [
                'id'        => $d->id,
                'name'      => $d->name,
                'status'    => __('defaults.no_connection'),
                'last_seen' => $d->created_at->timezone('Asia/Manila'),
                'icon'      => 'mdi:alert-circle-outline',
            ]);

        $switchbotDevicesBattery = SwitchBotDevice::query()
            ->withWhereHas('latestData', function ($q) {
                $q->where('context->battery', '<', 19);
            })
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'status' => __('defaults.low_battery'),
                'last_seen' => optional($d->latestData?->created_at)?->timezone('Asia/Manila'),
                'icon'      => 'mdi:battery-10',
            ]);

        return $lorawanDevices->concat($switchbotDevicesNoData)->concat($switchbotDevicesBattery)->sortByDesc('last_seen')->values();
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> loadRecentActivity
     * <Function> Load recent activity logs.
     *
     * @return \Illuminate\Support\Collection
     */
    public function loadRecentActivity(): Collection
    {
        return Activity::whereIn('log_name', ['error', 'warning', 'information'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get(['id', 'log_name', 'description', 'created_at'])
            ->map(function ($item) {
                return [
                    'id'          => $item->id,
                    'type'        => $item->log_name,
                    'description' => $item->description,
                    'last_seen'   => $item->created_at->tz('Asia/Manila'),
                    'icon'        => match ($item->log_name) {
                        'error'       => 'material-symbols:error-outline-rounded',
                        'warning'     => 'material-symbols:warning-outline-rounded',
                        'information' => 'material-symbols:info-outline-rounded',
                        default       => 'material-symbols:info-outline-rounded',
                    },
                ];
            })
            ->values();
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> loadDailyEnergyCost
     * <Function> Load daily energy cost for dashboard.
     *
     * @return \Illuminate\Support\Collection
     */
    public function loadDailyEnergyCost($month = null): Collection
    {

        $period      = $month === 'last_month' ? now('Asia/Manila')->subMonth() : now('Asia/Manila');
        $start       = $period->copy()->startOfMonth()->startOfDay()->utc();
        $end         = $period->copy()->endOfMonth()->endOfDay()->utc();
        $rate        = ElectricityRate::effectiveRate($period);
        $hasRate     = $rate !== null;
        $rate        = $rate ?? 0;

        $labels = collect(CarbonPeriod::create($period->copy()->startOfMonth(), '1 day', $period->copy()->endOfMonth()))
            ->map->format('Y-m-d');

        $records = HourlyConsumption::select('date', 'consumption')
            ->whereBetween('date', [$start->toDateTimeString(), $end->toDateTimeString()])
            ->where('consumption->type', 'positive_active_energy')
            ->get();

        $costByDay        = [];
        $kwhByDay         = [];
        $gapDays          = [];
        $unattributedCost = 0.0;
        $unattributedKwh  = 0.0;

        foreach ($records as $record) {
            $consumption = $record->consumption;
            $dayStr      = Carbon::parse($record->date)->timezone('Asia/Manila')->format('Y-m-d');
            $kwh         = $consumption['value'];
            $cost        = $kwh * $rate;

            if (isset($consumption['hasGap']) === true && isset($consumption['gap_info'])) {
                $unattributedCost += $cost;
                $unattributedKwh  += $kwh;

                $cursor    = Carbon::parse($consumption['gap_info']['gap_start'])
                    ->timezone('Asia/Manila')
                    ->startOfDay();
                $gapEndDay = Carbon::parse($consumption['gap_info']['gap_end'])
                    ->timezone('Asia/Manila')
                    ->startOfDay();

                while ($cursor->lt($gapEndDay)) {
                    $gapDays[$cursor->format('Y-m-d')] = true;
                    $cursor->addDay();
                }
            } else {
                $costByDay[$dayStr] = ($costByDay[$dayStr] ?? 0) + $cost;
                $kwhByDay[$dayStr]  = ($kwhByDay[$dayStr] ?? 0) + $kwh;
            }
        }

        $measuredTotal = array_sum($costByDay);
        $measuredKwh   = array_sum($kwhByDay);

        return collect([[
            'name'             => $period->format('F'),
            'data'             => $labels->map(fn($date) => [
                $date,
                round($costByDay[$date] ?? 0, 2),
                !empty($gapDays[$date]),
                round($kwhByDay[$date] ?? 0, 2),
            ])->values(),
            'hasRate'          => $hasRate,
            'measuredCost'     => round($measuredTotal, 2),
            'unattributedCost' => round($unattributedCost, 2),
            'totalCost'        => round($measuredTotal + $unattributedCost, 2),
            'measuredKwh'      => round($measuredKwh, 2),
            'unattributedKwh'  => round($unattributedKwh, 2),
            'totalKwh'         => round($measuredKwh + $unattributedKwh, 2),
        ]]);
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> loadMonthlyTotals
     * <Function> Load monthly totals for dashboard.
     *
     * @return array
     */
    public function loadMonthlyTotals(): array
    {
        $manila = now('Asia/Manila');

        $calc = function ($startDate) {
            $period = $startDate->copy();
            $start  = $period->copy()->startOfMonth()->startOfDay()->utc();
            $end    = $period->copy()->endOfMonth()->endOfDay()->utc();
            $rate   = ElectricityRate::effectiveRate($period);
            $hasRate = $rate !== null;
            $rate   = $rate ?? 0;

            $records = HourlyConsumption::select('date', 'consumption')
                ->whereBetween('date', [$start->toDateTimeString(), $end->toDateTimeString()])
                ->where('consumption->type', 'positive_active_energy')
                ->get();

            $measuredCost     = 0.0;
            $unattributedCost = 0.0;
            $measuredKwh      = 0.0;
            $unattributedKwh  = 0.0;

            foreach ($records as $record) {
                $consumption = $record->consumption;
                $kwh         = $consumption['value'];
                $cost        = $kwh * $rate;

                if (isset($consumption['hasGap']) === true && isset($consumption['gap_info'])) {
                    $unattributedCost += $cost;
                    $unattributedKwh  += $kwh;
                } else {
                    $measuredCost += $cost;
                    $measuredKwh  += $kwh;
                }
            }

            return [
                'hasRate'          => $hasRate,
                'cost'             => round($measuredCost + $unattributedCost, 2),
                'measuredCost'     => round($measuredCost, 2),
                'unattributedCost' => round($unattributedCost, 2),
                'kwh'              => round($measuredKwh + $unattributedKwh, 2),
                'measuredKwh'      => round($measuredKwh, 2),
                'unattributedKwh'  => round($unattributedKwh, 2),
            ];
        };

        return [
            'current_month' => $calc($manila->copy()->startOfMonth()),
            'last_month'    => $calc($manila->copy()->subMonth()->startOfMonth()),
        ];
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> loadEnergyHourlyData
     * <Function> Load energy hourly data for dashboard.
     *
     * @param array $devices
     * @param string $overall
     * @param int|null $hours
     *
     * @return \Illuminate\Support\Collection
     */
    public function loadEnergyHourlyData(array $devices, string $overall, ?int $hours): Collection
    {
        if (empty($devices)) {
            return collect();
        }

        $end   = now()->utc()->startOfHour();
        $start = (clone $end)->subHours($hours - 1);

        $types = collect(
            $overall === 'over_all'
                ? ['over_all' => 'positive_active_energy']
                : [
                    'phase_a' => 'active_energy_a',
                    'phase_b' => 'active_energy_b',
                    'phase_c' => 'active_energy_c',
                ]
        );

        $labels = collect(CarbonPeriod::create($start, '1 hour', $end))
            ->map(fn($d) => $d->toISOString());

        return HourlyConsumption::whereBetween('date', [$start, $end])
            ->whereIn('device_id', (array) $devices)
            ->whereIn('consumption->type', $types->values())
            ->get()
            ->groupBy(fn($data) => $data->device_id . '.' . $data->consumption['type'])
            ->map(function ($group) use ($labels, $types) {

                $record = $group->first();
                $key = $types->search($record->consumption['type'], true) ?? $record->consumption['type'];
                $timestamp = $group->keyBy(fn($record) => Carbon::parse($record->date)->toISOString());

                // Build data array with point and metadata
                $data = $labels->map(function ($time) use ($timestamp) {
                    $pointData = $timestamp[$time]->consumption ?? null;

                    return [
                        'point'       => [$time, $pointData['value'] ?? 'No Data'],
                        'hasGap'      => $pointData['hasGap'] ?? false,
                        'data_status' => $pointData['data_status'] ?? 'complete',
                        'gap_info'    => $pointData['gap_info'] ?? null,
                    ];
                })->toArray();

                return [
                    'id'    => $record->device_id,
                    'name'  => $record->device?->context[$key] ?? $record->device?->name,
                    'alias' => $record->consumption['type'],
                    'latest_data' => Carbon::parse($record->max('updated_at'))->tz('Asia/Manila'),
                    'data'  => array_map(fn($item) => $item['point'], $data),
                    'series' => $data
                ];
            })->values();
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> loadEnergyMonthlyData
     * <Function> Load energy monthly data for dashboard.
     *
     * @param int|null $months
     *
     * @return \Illuminate\Support\Collection
     */
    public function loadEnergyMonthlyData(?int $months): Collection
    {
        $end    = now('Asia/Manila')->startOfMonth();
        $start  = (clone $end)->subMonths($months - 1);

        $labels = collect(CarbonPeriod::create($start, '1 month', $end))->map->format('Y-m');

        return MonthlyConsumption::whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->get()
            ->groupBy('floor_name')
            ->values()
            ->map(function ($g) use ($labels) {
                $by = $g->keyBy(fn($r) => Carbon::parse($r->date)->format('Y-m'));
                return [
                    'name'        => $g->first()->floor_name,
                    'latest_data' => $g->max('updated_at')->tz('Asia/Manila'),
                    'data' => $labels->map(function ($m) use ($by) {
                        $record = $by->get($m);
                        return [
                            'month'                    => $m,
                            'consumption'              => optional($record)->consumption,
                            'confirmed_consumption'    => optional($record)->confirmed_consumption,
                            'unattributed_consumption' => optional($record)->unattributed_consumption,
                            'has_unattributed'         => optional($record)->has_unattributed ?? false,
                        ];
                    })->all(),
                ];
            });
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> loadACMonitor
     * <Function> Load Aircon temperature monitoring per room.
     *
     * @param string $deviceId
     * @param int $day
     * @return array
     */
    public function loadACMonitor(string $deviceId, int $day): array
    {
        $dateThreshold = now()->subDays($day - 1)->startOfDay();

        $dataQuery = fn($q) => $q->where('received_at', '>=', $dateThreshold)
            ->orderBy('received_at', 'asc');

        // Try LorawanDevice
        $device = LorawanDevice::with(['data' => fn($q) => $dataQuery($q)->where('type', 'temperature')])
            ->find($deviceId);

        if ($device?->data->isNotEmpty()) {
            return [[
                'id' => $device->id,
                'name' => $device->name,
                'area_name' => $device->area?->name,
                'data' => $device->data->map(fn($d) => [
                    $d->received_at->format('Y-m-d H:i:s'),
                    $d->context['value']
                ])->values()->toArray(),
            ]];
        }

        // Try SwitchBotRemote
        $device = SwitchBotRemote::with(['data' => $dataQuery])->find($deviceId);

        if ($device?->data->isNotEmpty()) {
            return [[
                'id' => $device->id,
                'name' => $device->name,
                'data' => $device->data->map(fn($d) => [
                    $d->received_at->format('Y-m-d H:i:s'),
                    $d->context['temperature']
                ])->values()->toArray(),
            ]];
        }

        return [];
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> loadRoomDevicesAndFilters
     * <Function> Load room with devices based on selected tab and filters for dashboard.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Services\MonitoringAndControls\MonitoringAndControlService $monitor
     * @return array
     */
    public function loadRoomDevicesAndFilters(Request $request, MonitoringAndControlService $monitor): array
    {
        $tab = $request->get('tab', 'aircon');

        $filters = $request->only(['search', 'sort', 'direction']) + [
            'room_id' => $request->room,
            'sort' => 'name',
            'direction' => 'asc'
        ];

        $airconList = $monitor->getAircon($filters);

        return [
            'activeTab'   => $tab,
            'queryParams' => $request->query(),
            'airconList' => collect($airconList->items())->map(fn($aircon) => [
                'id' => $aircon['id'],
                'name' => $aircon['name']
            ])->sortBy('name')->values()->toArray(),
            'aircons'     => fn() => $tab === 'aircon' ? $airconList : [],
            'switches'    => fn() => $tab === 'switches' ? $monitor->getSwitches($filters) : [],
            'appliances'  => fn() => $tab === 'appliances' ? $monitor->getPlugs($filters) : [],
        ];
    }

    /**
     * <Layer number> (17.0)
     *
     * <Processing name> loadElectricDataPerDevice
     * <Function> Load electric consumption data for a specific device.
     *
     * @param mixed $device
     * @return array|null
     */
    public function loadElectricDataPerDevice($device): ?array
    {
        if (!$device) {
            return null;
        }

        $consumptions = HourlyConsumption::with([
            'device:id,name,room_id,updated_at',
            'device.room:id,name',
            'device.latestData'
        ])
            ->where('device_id', $device)
            ->where('consumption->type', 'positive_active_energy')
            ->whereHas('device')
            ->select('id', 'device_id', 'consumption', 'created_at')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        $deviceModel = $consumptions->first()?->device;

        if (!$deviceModel) {
            return null;
        }

        return [
            'id' => $deviceModel->id,
            'name' => $deviceModel->name,
            'location' => $deviceModel->location?->name ?? 'N/A',
            'current' => $deviceModel->latestData?->context["total_current"]['value'] ?? 0,
            'voltage' => $deviceModel->latestData?->context["average_voltage_phase"]['value'] ?? 0,
            'last_updated_at' => $deviceModel->updated_at,
            'data' => $consumptions
                ->sortBy('created_at')
                ->map(function ($item) {
                    return [
                        $item->created_at->toISOString(),
                        $item->consumption['value'] ?? 0
                    ];
                })
                ->values()
        ];
    }

    /**
     * <Layer number> (18.0)
     *
     * <Processing name> loadCarbonFootprint
     * <Function> Load carbon footprint data for given device IDs.
     *
     * @param array $deviceIds
     * @return array|null
     */
    public function loadCarbonFootprint(array $deviceIds): ?array
    {
        if (empty($deviceIds)) {
            return null;
        }

        $manila = Carbon::now('Asia/Manila');

        $query = fn($start, $end) => HourlyConsumption::with('device')
            ->whereIn('device_id', $deviceIds)
            ->where('consumption->type', 'positive_active_energy')
            ->whereHas('device')
            ->whereBetween('date', [$start, $end])
            ->select('id', 'device_id', 'consumption', 'date')
            ->get();

        $current  = $query($manila->copy()->startOfMonth()->utc(), $manila->copy()->endOfMonth()->utc());
        $previous = $query($manila->copy()->subMonth()->startOfMonth()->utc(), $manila->copy()->subMonth()->endOfMonth()->utc());

        $summarize = fn($records) => $records
            ->groupBy('device_id')
            ->map(fn($r, $id) => [
                'id'   => $id,
                'name' => optional($r->first()->device)->name,
                'total_kwh'   => round($r->sum(fn($x) => data_get($x->consumption, 'value', 0)), 4),
                'carbon_kg'   => round($r->sum(fn($x) => data_get($x->consumption, 'value', 0)) * self::EMISSION_FACTOR, 4),
            ])
            ->values();

        $meters            = $summarize($current);
        $prevTotalCarbonKg = $summarize($previous)->sum('carbon_kg');
        $totalCarbonKg     = $meters->sum('carbon_kg');

        return [
            'month'                => $manila->startOfMonth()->format('F Y'),
            'emission_factor'       => self::EMISSION_FACTOR,
            'target_kg'            => '10000',
            'total_carbon_kg'      => round($totalCarbonKg, 4),
            'prev_total_carbon_kg' => round($prevTotalCarbonKg, 4),
            'carbon_kg_change'     => round($totalCarbonKg - $prevTotalCarbonKg, 4),
            'meters'               => $meters,
        ];
    }

    /**
     * <Layer number> (19.0)
     *
     * <Processing name> loadPowerFactor
     * <Function> Load power factor data for given device IDs.
     *
     * @param array $deviceIds
     * @return \Illuminate\Support\Collection|null
     */
    public function loadPowerFactor(array $deviceIds): ?Collection
    {
        if (empty($deviceIds)) {
            return null;
        }

        return LorawanDevice::with(['latestData:id,device_id,context'])
            ->whereIn('id', $deviceIds)
            ->get(['id', 'name'])
            ->map(fn($m) => [
                'id'                 => $m->id,
                'name'               => $m->name,
                'power_factor' => $m->latestData?->context['power_factor_total']['value'] ?? null,
            ]);
    }

    /**
     * <Layer number> (20.0)
     *
     * <Processing name> loadVisitorCounter
     * <Function> Load visitor counter summary for dashboard.
     *
     * @return array
     */
    public function loadVisitorCounter(): array
    {
        $startOfDay = Carbon::now('Asia/Manila')->startOfDay()->utc();

        // Daily visitors (today)
        $dailyVisitors = HourlyVisitor::query()
            ->where('datetime', '>=', $startOfDay)
            ->sum('value');

        // Total visitors (all time)
        $totalVisitors = HourlyVisitor::sum('value');

        return  [
            'daily' => (int) $dailyVisitors,
            'total' => (int) $totalVisitors
        ];
    }

    /**
     * <Layer number> (21.0)
     *
     * <Processing name> loadVisitorGraphData
     * <Function> Load visitor graph data for dashboard.
     *
     * @return array
     */
    public function loadVisitorGraphData(): array
    {
        $startLocal = Carbon::now('Asia/Manila')->startOfDay();

        $rows = HourlyVisitor::query()
            ->where('datetime', '>=', $startLocal->copy()->utc())
            ->pluck('value', 'datetime');

        return collect(range(0, 23))->map(function ($i) use ($startLocal, $rows) {
            $t = $startLocal->copy()->addHours($i);
            $key = $t->copy()->utc()->format('Y-m-d H:00:00');

            return [$t->format('Y/m/d H:i'), (int)($rows[$key] ?? 0)];
        })->all();
    }

    /**
     * <Layer number> (22.0)
     *
     * <Processing name> loadCorsightDashboardData
     * <Function> Load the Corsight CCTV dashboard payload (recent visit
     *            logs, per-room camera and occupancy summary, per-camera
     *            dwell time, camera list) for the selected time range.
     *
     * <Notes> Source is the projected person sessions (one row per camera
     *         visit) and the device registry; raw event context is never
     *         parsed here. Face crops are returned only for the newest 30
     *         rows to keep the payload small. $range is one of "today",
     *         "last_7_days", "last_30_days" (unknown values fall back to
     *         the 30-day window).
     *
     * @param  string  $range
     * @return array
     */
    public function loadCorsightDashboardData(string $range = 'last_30_days'): array
    {
        $windowStart = match ($range) {
            'today' => now()->startOfDay(),
            'last_7_days' => now()->subDays(7),
            default => now()->subDays(30),
        };

        $sessions = CorsightPersonSession::query()
            ->countable()
            ->where('enter_at', '>=', $windowStart)
            ->orderByDesc('enter_at')
            ->select(['id', 'status', 'enter_at', 'exit_at', 'last_seen_at', 'dwell_seconds', 'gender', 'age_group', 'device_id'])
            ->with('device:id,name,room_id', 'device.room:id,name')
            ->get();

        $imageIds = $sessions->slice(0, 30)->pluck('id');
        $images = $imageIds->isEmpty()
            ? collect()
            : CorsightPersonSession::query()
            ->whereIn('id', $imageIds)
            ->get(['id', 'image'])
            ->filter(fn($session) => filled($session->image))
            ->pluck('image', 'id');

        $devices = CorsightDevice::query()
            ->with('room:id,name')
            ->get();

        $deviceIds = $devices->pluck('id');

        $lastActivity = $deviceIds->isEmpty()
            ? collect()
            : CorsightDeviceData::query()
            ->whereIn('device_id', $deviceIds)
            ->groupBy('device_id')
            ->get(['device_id', DB::raw('max(received_at) as last_at')])
            ->pluck('last_at', 'device_id');

        $staleBefore = now()->subMinutes((int) config('corsight.health_stale_minutes', 30));

        $openByDevice = $deviceIds->isEmpty()
            ? collect()
            : CorsightPersonSession::query()
            ->countable()
            ->where('status', 'in')
            ->whereIn('device_id', $deviceIds)
            ->get(['device_id'])
            ->groupBy('device_id')
            ->map->count();

        $rooms = $devices
            ->groupBy(fn($device) => $this->corsightRoomLabel($device))
            ->map(function ($group) use ($lastActivity, $staleBefore, $openByDevice) {
                return [
                    'name' => $this->corsightRoomLabel($group->first()),
                    'active_cameras' => $group->filter(
                        fn($device) => $lastActivity->has($device->id)
                            && Carbon::parse($lastActivity->get($device->id))->gte($staleBefore)
                    )->count(),
                    'total_cameras' => $group->count(),
                    'occupancy' => (int) $group->sum(fn($device) => $openByDevice->get($device->id, 0)),
                ];
            })
            ->values();

        // Per camera (device) so the dashboard's device filter can narrow it.
        $dwellTime = $sessions
            ->filter(fn($session) => $session->device !== null)
            ->groupBy(fn($session) => $session->device_id)
            ->map(function ($group) {
                $closed = $group->where('status', '!=', 'in');
                $completed = $closed->whereNotNull('dwell_seconds');
                $latest = $closed->sortByDesc('enter_at')->first() ?? $group->sortByDesc('enter_at')->first();
                $endAt = $latest ? ($latest->exit_at ?? $latest->last_seen_at ?? now()) : null;

                return [
                    'device_id' => $group->first()->device_id,
                    'device_name' => $group->first()->device?->name,
                    'room' => $this->corsightRoomLabel($group->first()->device),
                    'average_dwell_seconds' => $completed->isEmpty() ? 0 : (int) round($completed->avg('dwell_seconds')),
                    'start' => $latest?->enter_at?->toIso8601ZuluString(),
                    'end' => $endAt?->toIso8601ZuluString(),
                    'duration_seconds' => $latest ? (int) abs($endAt->diffInSeconds($latest->enter_at)) : 0,
                ];
            })
            ->values();

        return [
            // Freshness of the data, not the request — sessions are ordered
            // enter_at desc, so the first row is the newest record.
            'last_updated' => $sessions->isEmpty() ? null : $sessions->first()->enter_at?->toIso8601ZuluString(),
            'recent_logs' => $sessions->map(fn($session) => [
                'id' => $session->id,
                'image' => $images->has($session->id) ? 'data:image/jpeg;base64,' . $images->get($session->id) : null,
                'gender' => $this->mapCorsightGender($session->gender),
                'age_range' => $this->mapCorsightAgeRange($session->age_group),
                'direction' => $session->status === 'in' ? 'entered' : 'exited',
                'device_id' => $session->device_id,
                'device_name' => $session->device?->name,
                'room' => $session->device ? $this->corsightRoomLabel($session->device) : null,
                'timestamp' => $session->enter_at?->toIso8601ZuluString(),
                'exit_at' => $session->exit_at?->toIso8601ZuluString(),
            ])->all(),
            'rooms' => $rooms->all(),
            'devices' => $devices->sortBy('name')->map(fn($device) => [
                'id' => $device->id,
                'name' => $device->name,
                'room' => $this->corsightRoomLabel($device),
            ])->values()->all(),
            'dwell_time' => $dwellTime->all(),
        ];
    }

    /**
     * <Layer number> (23.0)
     *
     * <Processing name> mapCorsightGender
     * <Function> Map a raw Fortify gender outcome to the dashboard enum.
     *
     * @param  string|null  $gender
     * @return string
     */
    private function mapCorsightGender(?string $gender): string
    {
        return match (strtolower(trim((string) $gender))) {
            'male' => 'male',
            'female' => 'female',
            default => 'unknown',
        };
    }

    /**
     * <Layer number> (24.0)
     *
     * <Processing name> mapCorsightAgeRange
     * <Function> Re-bucket a raw Fortify age group into the dashboard's
     *            10-year ranges; null when not determined.
     *
     * <Notes> Fortify emits 5-year groups (e.g. "20-24"); the lower bound
     *         decides the bucket. The exact vendor enum is verified against
     *         live rows once the cameras stream.
     *
     * @param  string|null  $ageGroup
     * @return string|null
     */
    private function mapCorsightAgeRange(?string $ageGroup): ?string
    {
        if (! filled($ageGroup) || strtolower(trim($ageGroup)) === 'not_determined') {
            return null;
        }

        if (! preg_match('/(\d+)/', $ageGroup, $matches)) {
            return null;
        }

        $lowerBound = (int) $matches[1];

        return match (true) {
            $lowerBound < 30 => '20-29',
            $lowerBound < 40 => '30-39',
            $lowerBound < 50 => '40-49',
            default => '50+',
        };
    }

    /**
     * <Layer number> (25.0)
     *
     * <Processing name> corsightRoomLabel
     * <Function> Location label shared by rooms[].name and
     *            recent_logs[].room — the iBMS room name when the device
     *            has one, otherwise the camera name.
     *
     * @param  \App\Models\CorsightDevice  $device
     * @return string
     */
    private function corsightRoomLabel(CorsightDevice $device): string
    {
        return $device->room?->name ?? $device->name;
    }
}

<?php

namespace App\Services\UserDashboard;

use App\Models\LorawanDevice;
use App\Models\Promo;
use App\Models\Room;
use App\Models\SwitchBotDevice;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UserDashboardService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct             (1.0)  Initialize Room instance from request.
    // getRoom                 (2.0)  Retrieve room with its areas.
    // getRoomSwitches         (3.0)  Retrieve switches with latest data.
    // getRoomThermostat       (4.0)  Retrieve thermostats with latest data.
    // getRoomPeopleCounter    (5.0)  Retrieve people counter devices.
    // getRoomSensors          (6.0)  Retrieve sensor devices.
    // getRoomOccuTempLight    (7.0)  Retrieve occupancy temperature light data.
    // getRoomCO2TempHumid     (8.0)  Retrieve CO2, temperature humidity data.
    // getRoomDevices          (9.0)  Retrieve generic SwitchBot devices.
    // getRoomCurtains         (10.0) Retrieve curtain devices with latest data.
    // getRoomBlindTilt        (11.0) Retrieve blind tilt devices.
    // getRoomOutdoorMeter     (12.0) Retrieve outdoor meter readings.
    // getRoomAircon           (13.0) Retrieve air conditioning remote devices.
    // getRoomProjector        (14.0) Retrieve projector remote devices.
    // getRoomTelevision       (15.0) Retrieve television remote devices.
    // getAllPromos            (16.0) Retrieve all promos.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Initialize Room instance from request parameter.
     *
     */
    public function __construct(private Room $room)
    {
        $this->room = Room::whereNumber(request('room'))->select('id', 'name', 'number')->firstOrFail();
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> getRoom
     * <Function> Retrieve room and its areas.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getRoom(): Room
    {
        return $this->room->load('roomAreas:id,room_id,name', 'scenes');
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> getRoomSwitches
     * <Function> Retrieve switches with latest data.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomSwitches(): Collection
    {
        return $this->room->lorawanDevices()
            ->deviceType('switches')
            ->inRoomArea(request('area'))
            ->with([
                'positions' => fn($q) =>
                $q->where('room_area', unslug(request('area') ?? 'all-area')),
                'latestData'
            ])
            ->get()
            ->filter->latestData
            ->map(fn($d) => collect([
                'id'          => $d->id,
                'name'        => $d->name,
                'model'       => $d->model,
                'context'     => $d->context,
                'positions'   => $d->positions,
                'latest_data' => $d->latestData?->context,
            ]))
            ->values();
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> getRoomThermostat
     * <Function> Retrieve thermostats with latest data.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomThermostat(): Collection
    {
        return $this->room->lorawanDevices()
            ->deviceType('thermostat')
            ->inRoomArea(request('area'))
            ->with([
                'positions' => fn($q) => $q->where('room_area', unslug(request('area') ?? 'all-area')),
                'latestData'
            ])
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'model' => $d->model,
                'context' => $d->context,
                'positions' => $d->positions,
                'latest_data' => $d->latestData?->context
            ])->whereNotNull('latest_data');
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> getRoomPeopleCounter
     * <Function> Retrieve people counter devices.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomPeopleCounter(): Collection
    {
        $peopleCounters = $this->room->lorawanDevices()
            ->deviceType('peopleCounter')
            ->inRoomArea(request('area'))
            ->with([
                'positions' => fn($q) => $q->where('room_area', unslug(request('area') ?? 'all-area')),
                'latestData'
            ])
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'model' => $d->model,
                'context' => $d->context,
                'room' => ['name' => $d->room->name, 'room_area' => optional($d->roomArea)->name ?? ""],
                'positions' => $d->positions,
                'latest_data' => $d->latestData?->context
            ])
            ->whereNotNull('latest_data')
            ->values();

        if ($dailyOccupancy = $this->room->dailyOccupancy()
            ->whereDate('date', now()->setTimezone('Asia/Manila'))->first()
        ) {
            $peopleCounters->push([
                'id' => $dailyOccupancy->id,
                'name' => 'Daily Occupancy',
                'room' => ['name' => $this->room->name],
                'latest_data' => [$dailyOccupancy]
            ]);
        }

        return $peopleCounters;
    }
    /**
     * <Layer number> (6.0)
     *
     * <Processing name> getRoomSensors
     * <Function> Retrieve sensor devices.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomSensors(): Collection
    {
        return $this->room->lorawanDevices()
            ->deviceType('sensors')
            ->inRoomArea(request('area'))
            ->with([
                'positions' => fn($q) => $q->where('room_area', unslug(request('area') ?? 'all-area')),
                'latestData'
            ])
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'model' => $d->model,
                'context' => $d->context,
                'room' => [
                    'name' => $d->room->name,
                    'room_area' => optional($d->roomArea)->name ?? ""
                ],
                'positions' => $d->positions,
                'latest_data' => $d->latestData?->context
            ])->whereNotNull('latest_data')
            ->values();
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> getRoomOccuTempLight
     * <Function> Retrieve occupancy temperature light data.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomOccuTempLight(): Collection
    {
        $thresholds = [
            'temperature' => ['min' => 15, 'max' => 35],
            'illuminance' => ['min' => 400, 'max' => 1000],
        ];

        return $this->room->lorawanDevices()
            ->deviceType('occuTempLight')
            ->inRoomArea(request('area'))
            ->with(['positions' => fn($q) => $q->where('room_area', unslug(request('area') ?? 'all-area'))])
            ->get()
            ->tap(fn($devices) => LorawanDevice::loadTopNData($devices, 10))
            ->map(function ($d) use ($thresholds) {
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'model' => $d->model . '-g',
                    'positions' => $d->positions,
                    'series' => collect(['temperature', 'illuminance'])->map(function ($key) use ($d, $thresholds) {
                        return [
                            'name' => $key,
                            'params' => [
                                'min' => $thresholds[$key]['min'],
                                'max' => $thresholds[$key]['max'],
                            ],
                            'data' => $d->top_data->where('type', $key)->map(function ($entry) {
                                return [
                                    Carbon::parse($entry->received_at),
                                    $entry->context['value'] ?? null,
                                ];
                            })->values(),

                        ];
                    })
                ];
            })->filter(fn($d) => $d['series']->contains(fn($s) => $s['data']->isNotEmpty()));
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> getRoomCO2TempHumid
     * <Function> Retrieve CO2, temperature humidity data with thresholds.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomCO2TempHumid(): Collection
    {
        $thresholds = [
            'temperature' => ['min' => 15, 'max' => 35],
            'humidity' => ['min' => 400, 'max' => 1000],
            'carbon_dioxide' => ['min' => 200, 'max' => 700],
        ];

        return $this->room->lorawanDevices()
            ->deviceType('co2TempHumid')
            ->inRoomArea(request('area'))
            ->with(['positions' => fn($q) => $q->where('room_area', unslug(request('area') ?? 'all-area'))])
            ->get()
            ->tap(fn($devices) => LorawanDevice::loadTopNData($devices, 10))
            ->map(function ($d) use ($thresholds) {
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'model' => $d->model . '-g',
                    'positions' => $d->positions,
                    'series' => collect(['temperature', 'humidity', 'carbon_dioxide'])->map(function ($key) use ($d, $thresholds) {
                        return [
                            'name' => $key,
                            'params' => [
                                'min' => $thresholds[$key]['min'],
                                'max' => $thresholds[$key]['max'],
                            ],
                            'data' => $d->top_data->where('type', $key)->map(function ($entry) {
                                return [
                                    Carbon::parse($entry->received_at),
                                    $entry->context['value'] ?? null,
                                ];
                            })->values(),

                        ];
                    })
                ];
            })->filter(fn($d) => $d['series']->contains(fn($s) => $s['data']->isNotEmpty()));
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> getRoomDevices
     * <Function> Retrieve generic SwitchBot devices.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomDevices(): Collection
    {
        return $this->room->switchBotDevices()
            ->ofType('Plug', 'Plug Mini (JP)', 'Plug Mini (US)', 'Smart Lock', 'Smart Lock Pro', 'Bot')
            ->inRoomArea(request('area'))
            ->with(['latestData', 'positions' => fn($q) => $q->where('room_area', unslug(request('area') ?? 'all-area'))])
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'type' => $d->type->name,
                'context' => $d->context,
                'positions' => $d->positions,
                'latest_data' => $d->latestData?->context
            ]);
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> getRoomCurtains
     * <Function> Retrieve curtain devices with latest data.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomCurtains(): Collection
    {
        return $this->room->switchBotDevices()
            ->ofType('Curtain')
            ->where('context->master', true)
            ->inRoomArea(request('area'))
            ->with(['latestData', 'positions' => fn($q) => $q->where('room_area', unslug(request('area') ?? 'all-area'))])
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'type' => $d->type->name,
                'context' => $d->context,
                'positions' => $d->positions,
                'latest_data' => $d->latestData?->context
            ]);
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> getRoomBlindTilt
     * <Function> Retrieve blind tilt devices.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomBlindTilt(): Collection
    {
        return $this->room->switchBotDevices()
            ->ofType('Blind Tilt')
            ->where('context->master', true)
            ->inRoomArea(request('area'))
            ->with(['latestData', 'positions' => fn($q) => $q->where('room_area', unslug(request('area') ?? 'all-area'))])
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'type' => $d->type->name,
                'context' => $d->context,
                'positions' => $d->positions,
                'latest_data' => $d->latestData?->context
            ]);
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> getRoomOutdoorMeter
     * <Function> Retrieve outdoor meter readings with series data.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomOutdoorMeter(): Collection
    {
        return $this->room->switchBotDevices()
            ->ofType('WoIOSensor')
            ->inRoomArea(request('area'))
            ->with(['positions' => fn($q) => $q->where('room_area', unslug(request('area') ?? 'all-area'))])
            ->get()
            ->tap(fn($devices) => SwitchBotDevice::loadTopNData($devices, 10))
            ->map(function ($d) {
                return [
                    'id' => $d->id,
                    'name' => $d->name,
                    'context' => $d->context,
                    'type' => $d->type->name,
                    'positions' => $d->positions,
                    'series' => collect(['temperature', 'humidity'])->map(function ($key) use ($d) {
                        return [
                            'name' => $key,
                            'data' => $d->top_data->map(function ($entry) use ($key) {
                                return [
                                    Carbon::parse($entry->received_at),
                                    $entry->context[strtolower($key)] ?? null
                                ];
                            })->values(),
                        ];
                    })
                ];
            })->filter(fn($d) => $d['series']->contains(fn($s) => $s['data']->isNotEmpty()));
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> getRoomAircon
     * <Function> Retrieve air conditioning remote devices.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomAircon(): Collection
    {
        return $this->room
            ->switchBotHubs()
            ->with(['remotes' => function ($q) {
                $q->ofType('Air Conditioner')
                    ->inRoomArea(request('area'))
                    ->with([
                        'latestData',
                        'positions' => fn($q) => $q->where('room_area', unslug(request('area') ?? 'all-area'))
                    ]);
            }])
            ->get()
            ->flatMap->remotes
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'type' => $d->type->name,
                'context' => $d->context,
                'positions' => $d->positions,
                'latest_data' => $d->latestData?->context
            ]);
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> getRoomProjector
     * <Function> Retrieve projector remote devices.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomProjector(): Collection
    {
        return $this->room
            ->switchBotHubs()
            ->with(['remotes' => function ($q) {
                $q->ofType('Others')
                    ->where('name', 'LIKE', '%projector%')
                    ->inRoomArea(request('area'))
                    ->with([
                        'latestData',
                        'positions' => fn($q) => $q->where('room_area', unslug(request('area') ?? 'all-area'))
                    ]);
            }])
            ->get()
            ->flatMap->remotes
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'type' => $d->type->name,
                'context' => $d->context,
                'positions' => $d->positions,
                'latest_data' => $d->latestData?->context
            ]);
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> getRoomTelevision
     * <Function> Retrieve television remote devices.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomTelevision(): Collection
    {
        return $this->room
            ->switchBotHubs()
            ->with(['remotes' => function ($q) {
                $q->ofType('Others')
                    ->where(function ($x) {
                        $x->where('name', 'LIKE', '%television%')
                            ->orWhere('name', 'LIKE', '%tv%');
                    })
                    ->inRoomArea(request('area'))
                    ->with([
                        'latestData',
                        'positions' => fn($q) => $q->where('room_area', unslug(request('area') ?? 'all-area'))
                    ]);
            }])
            ->get()
            ->flatMap->remotes
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'type' => $d->type->name,
                'context' => $d->context,
                'positions' => $d->positions,
                'latest_data' => $d->latestData?->context
            ]);
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> getAllPromos
     * <Function> Retrieve all promos.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllPromos(): Collection
    {
        $promos = Promo::all();

        return $promos;
    }
}

<?php

namespace App\Services\Scenes;

use App\Jobs\Lorawan\SaveDeviceCommand;
use App\Jobs\Lorawan\SendDeviceCommand as LorawanSendDeviceCommand;
use App\Jobs\SwitchBot\SendDeviceCommand as SwitchBotSendDeviceCommand;
use App\Jobs\SwitchBot\SendRemoteCommand;
use App\Models\Activity;
use App\Models\LorawanDevice;
use App\Models\Room;
use App\Models\Scene;
use App\Models\SceneDevice;
use App\Models\SwitchBotDevice;
use App\Models\SwitchBotRemote;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;

class ScenesService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $room                            (1.0)  The room associated with the 
    //                                         authenticated user.
    // $scene                           (2.0)  The scene to be executed.
    // $filters                         (3.0)  Filters from request which 
    //                                         should be applied 
    //                                         upon loading devices.
    // __construct                      (4.0)  Define constructor of 
    //                                         Scenesservice to accept
    //                                         dashboard service
    // getPaginatedScenes               (5.0)  Get paginated list of scenes.
    // store                            (6.0)  Store a newly created scene and 
    //                                         its associated
    //                                         devices.
    // update                           (7.0)  Update an existing scene and its 
    //                                         associated devices.
    // convertScheduleTime              (8.0)  Converts a given schedule time 
    //                                         from the configured timezone to 
    //                                         UTC.
    // saveSceneDevice                  (9.0)  Save a device associated with a 
    //                                         scene in the scenes_devices table.
    // getDevicesInRoom                 (10.0)  Get all devices in the room.
    // getRoomSBAircons                 (11.0) Get the associated aircons with 
    //                                         the room via switchBot Remotes.
    // getRoomPlugs                     (12.0) Get the associated entities in 
    //                                         the room that
    // getRoomBots                      (13.0) Get the associated bot devices 
    //                                         of the room.
    // getRoomCurtains                  (14.0) Get the associated curtains with 
    //                                         the room.
    // getRoomBlindTilters              (15.0) Get the associated tilters with 
    //                                         the room .
    // getRoomThermostats               (16.0) Get the associated thermostats 
    //                                         with the room.
    // getRoomTelevisions               (17.0) Get the associated televisions 
    //                                         with the room.
    // getRoomProjectors                (18.0) Get the associated projectors 
    //                                         with the room.
    // getRoomSwitches                  (19.0) Get the associated switches with 
    //                                         the room.
    // getRoomSBLocks                   (20.0) Get the associated locks with 
    //                                         the room.
    // executeCommand                   (21.0) Execute a command on a given 
    //                                         device based on its type.
    // executeDeviceCommand             (22.0) Execute a command on a given 
    //                                         device.
    // handleLorawanCommand             (23.0) Handle command execution for 
    //                                         Lorawan devices.
    // handleSwitchBotCommand           (24.0) Handle command execution for 
    //                                         SwitchBot devices.
    // handleSwitchBotRemoteCommand     (25.0) Handle command execution for 
    //                                         SwitchBot remotes.
    // getDeviceType                    (26.0) Get the device type of a 
    //                                         SwitchBot remote.
    // executeCheckInOut                (27.0) Execute a command on a given 
    //                                         scene.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $room
     * <Function> The room associated with the authenticated user.
     *
     * @var \App\Models\Room
     */
    public $room;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $scene
     * <Function> The scene to be executed.
     *
     * @var string
     */
    private $scene;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $filters
     * <Function> Filters from request which should be applied upon loading devices.
     *
     * @var SupportCollection
     */
    private $filters;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> __construct
     * <Function> Define constructor of Scenesservice to accept dashboard
     *            service
     */
    public function __construct(?SupportCollection $filters = null)
    {
        $this->filters = $filters ?? collect();
    }

    /**
     * <Layer Number> (5.0)
     *
     * <Processing Name> getPaginatedScenes
     * <Function> Get a paginated list of scenes.
     * 
     * @param array $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getPaginatedScenes(array $filters): LengthAwarePaginator
    {
        $sortField     = $filters['sort']      ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'asc';

        $sortMap = [
            'scene_name'  => fn($q) => $q->orderBy('name', $sortDirection),
            'created_at'  => fn($q) => $q->orderBy('created_at', $sortDirection),
            'room_name'   => fn($q) => $q->withAggregate('room', 'name')
                ->orderBy('room_name', $sortDirection),
            'device_name' => fn($q) => $q->withMin('sceneDevices', 'name')
                ->orderBy('scene_devices_min_name', $sortDirection),
        ];

        return Scene::query()
            ->with(['room', 'sceneDevices'])
            ->when(
                $filters['roomFilter'] ?? null,
                fn($q, $roomId) =>
                $q->where('room_id', $roomId)
            )
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhereHas('room', fn($r) => $r->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('sceneDevices', fn($d) => $d->where('device_id', 'like', "%{$search}%"));
                });
            })
            ->when(
                isset($sortMap[$sortField]),
                $sortMap[$sortField],
                fn($q) =>
                $q->orderBy('created_at', $sortDirection)
            )
            ->paginate(10)
            ->withQueryString();
    }

    /**
     * <Layer Number> (6.0)
     *
     * <Processing Name> storeScene
     * <Function> Store a newly created scene and associated devices.
     */
    public function storeScene(array $validated)
    {

        return DB::transaction(function () use ($validated) {
            $scene = Scene::create([
                'id'        => Str::uuid(),
                'context'   => ['palette' => $validated['color'], 'icon' => $validated['icon']],
                'name'      => $validated['name'],
                'room_id'   => $validated['room_id'],
                'scheduled' => $validated['scheduled'],
                'days'      => json_encode($validated['days'] ?? []),
                'time'      => $this->convertScheduleTime($validated['time'] ?? null),
            ]);

            foreach ($validated['commands'] ?? [] as $command) {
                $this->saveSceneDevice($scene, $command);
            }

            return $scene;
        });
    }

    /**
     * <Layer Number> (7.0)
     *
     * <Processing Name> updateScene
     * <Function> Update an existing scene and its associated devices.
     *
     */
    public function updateScene(Scene $scene, array $validated)
    {
        return DB::transaction(function () use ($scene, $validated) {
            $scene->update([
                'context'   => ['palette' => $validated['color'], 'icon' => $validated['icon']],
                'name'      => $validated['name'],
                'room_id'   => $validated['room_id'] ?? $scene->room_id,
                'scheduled' => $validated['scheduled'],
                'days'      => json_encode($validated['days'] ?? []),
                'time'      => $this->convertScheduleTime($validated['time'] ?? null),
            ]);

            foreach ($validated['commands'] ?? [] as $command) {
                $this->saveSceneDevice($scene, $command);
            }

            $keepPairs = collect($validated['commands'] ?? [])
                ->map(fn($c) => $c['id'] . '|' . $c['type'])
                ->toArray();

            $scene->sceneDevices()->get()->each(function ($sceneDevice) use ($keepPairs) {
                $pair = $sceneDevice->device_id . '|' . $sceneDevice->command_type;
                if (!in_array($pair, $keepPairs)) {
                    $sceneDevice->delete();
                }
            });

            return $scene;
        });
    }

    /**
     * <Layer Number> (8.0)
     *
     * <Processing Name> convertScheduleTime
     * <Function> Converts a given schedule time from the configured timezone to UTC.
     *
     */
    public function convertScheduleTime(?string $time)
    {
        if (!filled($time)) return null;

        return Carbon::parse($time, config('timezone.default'))
            ->utc()
            ->format('H:i:s');
    }

    /**
     * <Layer Number> (9.0)
     *
     * <Processing Name> saveSceneDevice
     * <Function> Save a device associated with a scene in the scenes_devices table.
     *
     * @param  \App\Models\Scene $scene
     * @param  mixed $deviceModel
     * @param  array $gangState
     * @return void
     */
    public function saveSceneDevice($scene, $command)
    {
        $device = SwitchBotRemote::find($command['id'])
            ?? SwitchBotDevice::find($command['id'])
            ?? LorawanDevice::find($command['id']);

        if (!$device) return;

        $type = get_class($device);

        SceneDevice::updateOrCreate(
            [
                'scene_id'     => $scene->id,
                'device_id'    => $command['id'] ?? null,
                'command_type' => $command['type'] ?? null,
            ],
            [
                'id'          => Str::uuid(),
                'device_type' => $type,
                'scene_id'    => $scene->id,
                'name'        => $device->name,
                'command'     => $command['command'] ?? null,
            ]
        );
    }

    /**
     * <Layer Number> (10.0)
     *
     * <Processing Name> getDevicesInRoom
     * <Function> Get all devices in the room.
     *
     * @param  \App\Models\Room  $room
     * @return array
     */
    public function getDevicesInRoom(Room $room): array
    {
        $this->room = $room;

        $devices = [
            'switches'     => $this->getRoomSwitches(),
            'thermostats'  => $this->getRoomThermostats(),
            'curtains'     => $this->getRoomCurtains(),
            'blinds'       => $this->getRoomBlindTilt(),
            'aircons'      => $this->getRoomSBAircons(),
            'plugs'        => $this->getRoomPlugs(),
            'bots'         => $this->getRoomBots(),
            'televisions'  => $this->getRoomTelevisions(),
            'projectors'   => $this->getRoomProjectors(),
            'locks'        => $this->getRoomSBLocks($this->room->name),
        ];

        return $devices;
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> getRoomSBAircons
     * <Function> Get the associated aircons with the room via switchBot Remote
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomSBAircons(): SupportCollection
    {
        return $this->room
            ->switchBotHubs()
            ->with(['remotes' => function ($q) {
                $q->ofType('Air Conditioner')
                    ->inRoomArea(request('area'))
                    ->with('latestData', 'roomArea');
            }])
            ->get()
            ->flatMap(function ($hub) {
                return $hub->remotes;
            });
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> getRoomPlugs
     * <Function> Get the associated entities in the room that are considered as
     *            plugs.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRoomPlugs(): EloquentCollection
    {
        return
            $this->room->switchBotDevices()
            ->ofType('Plug', 'Plug Mini (JP)', 'Plug Mini (US)')
            ->inRoomArea($this->filters->get('area'))
            ->with('latestData', 'roomArea')
            ->get();
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> getRoomBots
     * <Function> Get the associated bot devices of the current room.
     *
     * @return \App\Models\SwitchBotDevice|null
     */
    public function getRoomBots(): EloquentCollection
    {
        return $this->room
            ->switchBotDevices()
            ->ofType('Bot')
            ->inRoomArea($this->filters->get('area'))
            ->with('latestData', 'roomArea')
            ->get();
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> getRoomCurtains
     * <Function> Get the associated entities in the room that are considered as
     *            a curtain.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRoomCurtains(): EloquentCollection
    {
        return $this->room->switchBotDevices()
            ->ofType('Curtain')
            ->where('context->master', true)
            ->inRoomArea(request('area'))
            ->with('latestData', 'roomArea')
            ->get();
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> getRoomBlindTilt
     * <Function> Retrieve blind tilt devices.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomBlindTilt(): EloquentCollection
    {
        return $this->room->switchBotDevices()
            ->ofType('Blind Tilt')
            ->where('context->master', true)
            ->inRoomArea(request('area'))
            ->with('latestData', 'roomArea')
            ->get();
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> getRoomThermostats
     * <Function> Get the associated thermostats with the room
     *
     */
    public function getRoomThermostats()
    {
        return $this->room->lorawanDevices()
            ->deviceType('thermostat')
            ->inRoomArea(request('area'))
            ->with(['roomArea', 'latestData'])
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'model' => $d->model,
                'context' => $d->context,
                'room' => $d->room->id,
                'room_area'  => $d->roomArea,
                'latest_data' => $d->latestData?->context
            ])->whereNotNull('latest_data');
    }

    /**
     * <Layer number> (17.0)
     *
     * <Processing name> getRoomTelevisions
     * <Function> Get the associated entities in the room that are considered as
     *            a television.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRoomTelevisions(): EloquentCollection
    {
        return $this->room
            ->switchBotRemotes()
            ->ofType('Others')
            ->where(fn($query) => $query
                ->where('switch_bot_remotes.name', 'LIKE', '%tv%')
                ->orWhere('switch_bot_remotes.name', 'LIKE', '%television%'))
            ->inRoomArea($this->filters->get('area'))
            ->with('latestData', 'roomArea')
            ->get();
    }

    /**
     * <Layer number> (18.0)
     *
     * <Processing name> getRoomProjectors
     * <Function> Get the associated entities in the room that are considered as
     *            a projector.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRoomProjectors(): EloquentCollection
    {
        return $this->room
            ->switchBotRemotes()
            ->ofType('Others')
            ->where(fn($query) => $query
                ->where('switch_bot_remotes.name', 'LIKE', '%projector%'))
            ->inRoomArea($this->filters->get('area'))
            ->with('latestData', 'roomArea')
            ->get();
    }

    /**
     * <Layer number> (19.0)
     *
     * <Processing name> getRoomSwitches
     * <Function> Get the associated entities in the room that are considered as
     *            wall switches.
     * @todo Add logic for fetching TPE device list
     *
     * @return \Illuminate\Support\Collection
     */
    public function getRoomSwitches(): Collection
    {
        return $this->room->lorawanDevices()
            ->deviceType('switches')
            ->inRoomArea(request('area'))
            ->with(['roomArea', 'latestData'])
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'model' => $d->model,
                'context' => $d->context,
                'room' => $d->room->id,
                'room_area' => $d->roomArea,
                'latest_data' => $d->latestData?->context
            ])->whereNotNull('latest_data');
    }

    /**
     * <Layer number> (20.0)
     *
     * <Processing name> getRoomSBLocks
     * <Function> Get the associated entities in the room that are considered as
     *            a sb locks.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRoomSBLocks(string $room): EloquentCollection
    {
        return $this->room
            ->switchBotDevices()
            ->with('latestData', 'roomArea')
            ->ofType('Smart Lock')
            ->get();
    }

    /**
     * <Layer Number> (21.0)
     *
     * <Processing Name> executeCommand
     * <Function> Execute a command on a given device based on its type.
     *
     */
    public function executeSceneCommands(Scene $scene): bool
    {
        $sceneDevices = SceneDevice::where('scene_id', $scene->id)->get();

        $hasError = false;

        $sceneDevices->each(function ($sceneDevice) use (&$hasError) {
            try {
                $device = $sceneDevice->device;

                if (!$device) {
                    activity()
                        ->inLog(Activity::TYPE_WARNING)
                        ->withProperties([
                            'device_id' => $sceneDevice->device_id,
                            'message' => trans('messages.error.E008.message', ['device' => $sceneDevice->device_id]),
                            'action' => trans('messages.error.E008.action')
                        ])
                        ->log(trans('log.scene.need_to_configure'));
                    $hasError = true;
                    return;
                }

                $this->executeDeviceCommand($device, $sceneDevice->command);
            } catch (\Exception $e) {
                activity()
                    ->inLog(Activity::TYPE_ERROR)
                    ->withProperties([
                        'device_id' => $sceneDevice->device_id,
                        'command' => $sceneDevice->command,
                        'error' => $e->getMessage(),
                        'stack_trace' => $e->getTraceAsString(),
                    ])
                    ->log(trans('log.scene.failed_to_execute'));
            }
        });

        return $hasError;
    }

    /**
     * <Layer Number> (22.0)
     *
     * <Processing Name> executeDeviceCommand
     * <Function> Execute a command on a given device.
     */
    public function executeDeviceCommand($device, $command)
    {
        if ($device instanceof LorawanDevice) {
            $this->handleLorawanCommand($device, $command);
        } elseif ($device instanceof SwitchBotDevice) {
            $this->handleSwitchBotCommand($device, $command);
        } elseif ($device instanceof SwitchBotRemote) {
            $this->handleSwitchBotRemoteCommand($device, $command);
        } else {
            throw new \Exception('Unsupported device type.');
        }
    }

    /**
     * <Layer Number> (23.0)
     *
     * <Processing Name> handleLorawanCommand
     * <Function> Handle command execution for Lorawan devices.
     */
    protected function handleLorawanCommand(LorawanDevice $device, $command)
    {
        Bus::chain([
            new LorawanSendDeviceCommand($device, $command),
            new SaveDeviceCommand($device, $command),
        ])->dispatch();
    }

    /**
     * <Layer Number> (24.0)
     *
     * <Processing Name> handleSwitchBotCommand
     * <Function> Handle command execution for SwitchBot devices.
     */
    protected function handleSwitchBotCommand(SwitchBotDevice $device, $command)
    {
        Bus::chain([
            new SwitchBotSendDeviceCommand($device, $command),
        ])->dispatch();
    }

    /**
     * <Layer Number> (25.0)
     *
     * <Processing Name> handleSwitchBotRemoteCommand
     * <Function> Handle command execution for SwitchBot remotes.
     */
    protected function handleSwitchBotRemoteCommand(SwitchBotRemote $device, $command)
    {
        $device->load('hub.room.switchBotAccount');
        $latestData = $device->latestData->context;

        $commandValue = is_array($command) ? $command['command'] ?? null : $command ?? null;
        $parameter = is_array($command) ? $command['parameter'] ?? 'default' : 'default';
        $type = is_array($command) ? $command['type'] ?? null : $command ?? null;

        $deviceType = $this->getDeviceType($device);

        switch ($deviceType) {
            case 'air conditioner':
                $newData = array_merge($latestData, [$commandValue => $parameter]);

                $apiParameter = implode(',', [
                    $newData['temperature'] ?? $latestData['temperature'] ?? 20,
                    $newData['mode'] ?? $latestData['mode'] ?? 1,
                    $newData['fanSpeed'] ?? $latestData['fanSpeed'] ?? '1',
                    $newData['powerState'] ?? $latestData['powerState'] ?? 'on',
                ]);
                break;

            case 'television':
                if ($type === 'POWER') {
                    $latestData['powerState'] = Str::lower($latestData['powerState']) === 'off' ? 'on' : 'off';
                }

                $newData = ['powerState' => $type];

                $apiParameter = implode(',', [
                    strtoupper($newData['powerState'] ?? $latestData['powerState'] ?? 'ON'),
                ]);

                $newData = $latestData;

                break;

            case 'projector':
                $newData = ['powerState' => $commandValue];

                $apiParameter = implode(',', [
                    strtoupper($newData['powerState'] ?? $latestData['powerState'] ?? 'ON'),
                ]);
                break;

            default:
                throw new \Exception("Unsupported device type: $deviceType");
        }

        $payload = [
            'commandType' => $deviceType === 'air conditioner' ? 'command' : 'customize',
            'command' => $deviceType === 'air conditioner' ? 'setAll' : $apiParameter,
            'parameter' => $deviceType === 'air conditioner' ? $apiParameter : $parameter,
        ];


        Cache::add("sbremotecontrol-{$device->id}", $newData['powerState'], 65);

        SendRemoteCommand::dispatch($device, $payload, $newData);
    }

    /**
     * <Layer Number> (26.0)
     *
     * <Processing Name> getDeviceType
     * <Function> Determine the device type based on the device's type or name.
     */
    private function getDeviceType(SwitchBotRemote $device)
    {
        $type = strtolower($device->type->name);

        if ($type === 'others') {
            $name = strtolower($device->name);

            if (is_array($name)) {
                $name = implode(' ', $name);
            }

            if (str_contains($name, 'tv') || str_contains($name, 'television')) {
                return 'television';
            } elseif (str_contains($name, 'projector')) {
                return 'projector';
            }
        }
        return $type;
    }

    /**
     * <Layer Number> (27.0)
     *
     * <Processing Name> executeCheckInOut
     * <Function> Execute a command on a given scene.
     */
    public function executeCheckInOut($user, $sceneName)
    {
        if ($user->room_id) {
            $scene = Scene::where('room_id', $user->room_id)
                ->whereRaw('LOWER(name) = ?', [Str::lower($sceneName)])
                ->first();

            if ($scene) {
                $this->executeSceneCommands($scene);
            }
        }
    }
}

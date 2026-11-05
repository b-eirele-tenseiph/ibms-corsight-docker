<?php

namespace App\Jobs\Monitoring;

use App\Events\Monitoring\ActiveRoomUpdated;
use App\Models\Room;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Cache, DB, Log};
use Throwable;

class HandleRoomAreaDeviceCounter implements ShouldQueue
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // CACHE_PREFIX          (1.0)  Cache namespace for per-room device counts.
    // CACHE_TTL             (2.0)  Cache lifetime in seconds.
    // TYPE_LD               (3.0)  Device type for lorawan.
    // TYPE_SBD              (4.0)  Device type for switchbot device.
    // TYPE_SBR              (5.0)  Device type: SwitchBot Remote.
    // TYPES                 (6.0)  Allowed device types.
    // TYPE_MAP              (7.0)  Maps model classes to device types.
    // room                  (8.0)  The room associated with the device.
    // deviceType            (9.0)  Resolved device type for the payload.
    // __construct           (10.0) Initializes room and device type from the
    //                              given data.
    // handle                (11.0) Execute the action.
    // getCacheKey           (12.0) Builds the cache key for a given type and
    //                              room.
    // computeCounts         (13.0) Delegates counting to a type-specific method.
    // countLorawanDevices   (14.0) Counts LoRaWAN devices and active ones in
    //                              the room.
    // countSwitchBotDevices (15.0) Counts SwitchBot plug/bot devices and active
    //                              ones.
    // countSwitchBotRemotes (16.0) Counts SwitchBot remotes (AC/TV/projector)
    //                              and active ones.
    // formatResult          (17.0) Formats standardized count response.
    // updateDeviceStatus    (18.0) Persists device status and updates
    //                              room/areas.
    // dispatchUpdateEvent   (19.0) Dispatches event after updating room/areas.
    // failed                (20.0) Logs job failure details.

    use Queueable;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> CACHE_PREFIX
     * <Function> Cache namespace for per-room device counts.
     *
     * @var string
     */
    private const CACHE_PREFIX = 'room_area_device_counts';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> CACHE_TTL
     * <Function> Cache lifetime in seconds.
     *
     * @var int
     */
    private const CACHE_TTL = 86400;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> TYPE_LD
     * <Function> Device type for lorawan.
     *
     * @var string
     */
    public const TYPE_LD = 'lorawan-device';

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> TYPE_SBD
     * <Function> Device type for switchbot device
     *
     * @var string
     */
    public const TYPE_SBD = 'switchbot-device';

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> TYPE_SBR
     * <Function> Device type: SwitchBot Remote.
     *
     * @var string
     */
    public const TYPE_SBR = 'switchbot-remote';

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> TYPES
     * <Function> Allowed device types.
     *
     * @var string[]
     */
    private const TYPES = [self::TYPE_LD, self::TYPE_SBD, self::TYPE_SBR];

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> TYPE_MAP
     * <Function> Maps model classes to device types.
     *
     * @var array[]
     */
    private const TYPE_MAP = [
        'App\Models\LorawanDevice' => self::TYPE_LD,
        'App\Models\SwitchBotDevice' => self::TYPE_SBD,
        'App\Models\SwitchBotRemote' => self::TYPE_SBR,
    ];

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> room
     * <Function> The room associated with the device.
     *
     * @var \App\Models\Room $room
     */
    protected Room $room;

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> deviceType
     * <Function> Resolved device type for the payload.
     *
     * @var string|null
     */
    protected ?string $deviceType;

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> __construct
     * <Function> Initializes room and device type from the given data.
     *
     * @param mixed $data
     */
    public function __construct(protected mixed $data)
    {
        $this->room = $data->device?->room ?? $data->remote?->room;
        $this->deviceType = self::TYPE_MAP[$data->device_type] ?? null;
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> handle
     * <Function> Execute the action.
     *
     * @return void
     */
    public function handle(): void
    {
        if (!in_array($this->deviceType, self::TYPES, true)) {
            Log::warning('HandleRoomAreaDeviceCounter: unsupported device type', ['type' => $this->deviceType]);
            return;
        }

        if ($this->room === null) {
            return;
        }

        // Clear only the cache key for the triggering device type
        Cache::lock("device-counts-lock:{$this->room->id}", 10)->block(5, function () {

            $results = collect(self::TYPES)->mapWithKeys(function ($t) {
                $value = $this->computeCounts($t);
                Cache::put($this->getCacheKey($t), $value, self::CACHE_TTL);
                return [$t => $value];
            });

            $this->updateDeviceStatus($results);
        });
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> getCacheKey
     * <Function> Builds the cache key for a given type and room.
     *
     * @param string $type Device type.
     * @return string
     */
    private function getCacheKey(string $type): string
    {
        return sprintf('%s:%d:%s', self::CACHE_PREFIX, $this->room->id, $type);
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> computeCounts
     * <Function> Delegates counting to a type-specific method.
     *
     * @param string $type
     * @return array
     */
    private function computeCounts(string $type): array
    {
        $method = match ($type) {
            self::TYPE_LD => 'countLorawanDevices',
            self::TYPE_SBD => 'countSwitchBotDevices',
            self::TYPE_SBR => 'countSwitchBotRemotes',
        };

        return $this->$method();
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> countLorawanDevices
     * <Function> Counts LoRaWAN devices and active ones in the room.
     *
     * @return array
     */
    private function countLorawanDevices(): array
    {
        $devices = $this->room->lorawanDevices()
            ->with('latestData')
            ->where(function ($q) {
                $q->whereIn('model', ['WS501', 'WS502', 'WS503'])
                    ->orWhere(function ($q) {
                        $q->where('model', 'UC300')
                            ->where('context->slave_model', 'RCF230CD');
                    });
            })
            ->get();

        $active = $devices->filter(
            fn($d) =>
            collect($d->latestData?->context ?? [])
                ->values()
                ->pluck('value')
                ->intersect(['high', 'on'])
                ->isNotEmpty()
        );

        return $this->formatResult($devices->count(), $active);
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> countSwitchBotDevices
     * <Function> Counts SwitchBot plug/bot devices and active ones in the room.
     *
     * @return array
     */
    private function countSwitchBotDevices(): array
    {
        $devices = $this->room->switchBotDevices()
            ->ofType('Plug', 'Plug Mini (JP)', 'Plug Mini (US)', 'Bot')
            ->with('latestData')
            ->get();

        $active = $devices->filter(fn($device) => in_array('on', [
            strtolower(($device->latestData?->context ?? [])['powerState'] ?? ''),
            strtolower(($device->latestData?->context ?? [])['power'] ?? '')
        ]));

        return $this->formatResult($devices->count(), $active);
    }

    /**
     * <Layer number> (17.0)
     *
     * <Processing name> countSwitchBotRemotes
     * <Function> Counts SwitchBot remotes (AC/TV/projector) and active ones.
     *
     * @return array
     */
    private function countSwitchBotRemotes(): array
    {
        $remotes = $this->room->switchBotHubs()
            ->with(['remotes' => function ($q) {
                $q->where(function ($w) {
                    $w->ofType('Air Conditioner')
                        ->orWhere(fn($x) => $x->ofType('Others')->where('name', 'like', '%projector%'))
                        ->orWhere(fn($x) => $x->ofType('Others')->where('name', 'like', '%television%')->orWhere('name', 'like', '%tv%'));
                })->with('latestData');
            }])
            ->get()
            ->flatMap(function ($hub) {
                return $hub->remotes->map(function ($r) use ($hub) {
                    $r->room_area_id = $hub->room_area_id;
                    return $r;
                });
            });

        $active = $remotes->filter(
            fn($r) =>
            strtolower((string) data_get($r, 'latestData.context.powerState', '')) === 'on'
        );

        return $this->formatResult($remotes->count(), $active);
    }

    /**
     * <Layer number> (18.0)
     *
     * <Processing name> formatResult
     * <Function> Formats standardized count response.
     *
     * @param int $total Total devices.
     * @param \Illuminate\Support\Collection $activeCollection
     * @return array
     */
    private function formatResult(int $total, Collection $activeCollection): array
    {
        return [
            'total' => $total,
            'active' => $activeCollection->count(),
            'activeAreas' => $activeCollection->pluck('room_area_id')->all(),
            'computed_at' => now()->toIso8601String(),
        ];
    }

    /**
     * <Layer number> (19.0)
     *
     * <Processing name> updateDeviceStatus
     * <Function> Persists device status and updates room/areas.
     *
     * @param \Illuminate\Support\Collection $results
     * @return void
     */
    private function updateDeviceStatus(Collection $results): void
    {
        $stats = [
            'active' => $results->sum('active'),
            'total' => $results->sum('total'),
            'activeAreaIds' => $results->flatMap->activeAreas->unique()->values()
        ];

        DB::transaction(function () use ($stats) {
            // Update room device status
            $this->room->update([
                'device_status' => ['active' => $stats['active'], 'total' => $stats['total']]
            ]);

            // Reset all areas, then mark active ones
            $this->room->roomAreas()->update(['has_active_device' => false]);
            if ($stats['activeAreaIds']->isNotEmpty()) {
                $this->room->roomAreas()
                    ->whereIn('id', $stats['activeAreaIds'])
                    ->update(['has_active_device' => true]);
            }

            DB::afterCommit(fn() => $this->dispatchUpdateEvent($stats));
        });
    }

    /**
     * <Layer number> (20.0)
     *
     * <Processing name> dispatchUpdateEvent
     * <Function> Dispatches event after updating room/areas.
     *
     * @param array $status
     * @return void
     */
    private function dispatchUpdateEvent(array $status): void
    {
        ActiveRoomUpdated::dispatch([
            'room_id' => $this->room->id,
            'room_area_id' => $status['activeAreaIds']->toArray(),
            'device_status' => ['active' => $status['active'], 'total' => $status['total']],
        ]);
    }

    /**
     * <Layer number> (21.0)
     *
     * <Processing name> failed
     * <Function> Logs job failure details.
     *
     * @param \Throwable $e Exception instance.
     * @return void
     */
    public function failed(Throwable $e): void
    {
        Log::error('RoomDeviceCounter Failed', [
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}

<?php

namespace App\Services\SwitchBotDevice;

use App\Events\SwitchBot\ResourcesSyncing;
use App\Exceptions\SwitchBot\SynchronizationException;
use App\Models\Activity;
use App\Models\Room;
use App\Models\SwitchBotAccount;
use App\Models\SwitchBotDevice;
use App\Models\SwitchBotHub;
use App\Models\SwitchBotType;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncDeviceService
{
    /**************************************************************************/
    /* Processing hierarchy                                                   */
    /**************************************************************************/
    // RESOURCE_TYPE          (1.0)  The Switch Bot hub resource type.
    // $accounts              (2.0)  The SwitchBotAccount collection.
    // $types                 (3.0)  The SwitchBotType collection.
    // $devices               (4.0)  The devices collection from each accounts.
    // __construct            (5.0)  The constructor method.
    // handle                 (6.0)  Execute the syncing.
    // getNewDevices          (7.0)  Get new devices from scanned results.
    // getOutdatedDevices     (8.0)  Get outdated devices from scanned results.
    // getUpdatedDevices      (9.0)  Get updated devices from scanned results.
    // isResponseValid        (10.0) Validate the API response and handle
    //                               exceptions.
    // collectDevices         (11.0) Collect the devices from the response.
    // collectHubs            (12.0) Collect the hubs from the response.
    // getFailureReason       (13.0) Get the failure reason from the response.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> RESOURCE_TYPE
     * <Function> The Switch Bot device resource type.
     *
     * @var string
     */
    const RESOURCE_TYPE = 'device';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $accounts
     * <Function> The SwitchBotAccount collection.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $accounts;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $types
     * <Function> The SwitchBotType collection.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    protected $types;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $devices
     * <Function> The devices collection from each accounts.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $devices;

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> __construct
     * <Function> The constructor method.
     */
    public function __construct()
    {
        // get all SwitchBot accounts
        $this->accounts = SwitchBotAccount::all();

        // get all SwitchBot types
        $this->types = SwitchBotType::all();

        // Container for scanned devices
        $this->devices = collect();
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> handle
     * <Function> Execute the syncing.
     *
     */
    public function handle()
    {
        // Dispatch current status => scanning
        ResourcesSyncing::dispatch(self::RESOURCE_TYPE, __('defaults.scan_device'));

        $responses = Http::pool(function (Pool $pool) {
            return $this->accounts->map(
                fn($account) =>
                $pool->as($account->room_id)
                    ->switchBotPending($account->access_token, $account->secret_key)
                    ->get('/devices')
            )->toArray();
        });

        // Dispatch current status => analyzing
        ResourcesSyncing::dispatch(self::RESOURCE_TYPE, __('defaults.analyze_device'));

        // Track failed accounts separately
        $failed = collect();

        // perform operation on each response
        foreach ($responses as $roomId => $response) {

            $room = Room::with('switchBotAccount')->find($roomId);

            // Skip failed account — log and continue
            if (!$this->isResponseValid($response, $room?->name)) {
                $failed->push([
                    'room_id'   => $roomId,
                    'room_name' => $room?->name ?? trans('defaults.unknown'),
                    'reason'    => $this->getFailureReason($response),
                    'status'    => $response->status(),
                ]);
                continue;
            }

            // update the last_used_at attribute of the account
            $room->switchBotAccount()->update([
                'last_synced_at' => now()
            ]);

            // collect devices from each response
            $this->collectDevices($room, $response->json());
        }

        // Copy of all fetched devices
        $list = $this->devices;

        // Store newly fetched devices here
        $new = $this->getNewDevices();

        // Store outdated or devices to be removed here
        $outdated = $this->getOutdatedDevices();

        // Store updated devices here
        $updated = $this->getUpdatedDevices();

        return compact('list', 'new', 'outdated', 'updated', 'failed');
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> getNewDevices
     * <Function> Get new devices from scanned results.
     *
     * @return array
     */
    private function getNewDevices(): Collection
    {
        $serials = $this->devices->pluck('serial_number');

        $existingDevices = SwitchBotDevice::whereIn('serial_number', $serials)
            ->pluck('serial_number');

        $newDevices = $this->devices->filter(function ($device) use ($existingDevices) {
            return !$existingDevices->contains(strtoupper($device["serial_number"]));
        });

        return $newDevices->values();
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> getOutdatedDevices
     * <Function> Get outdated devices from scanned results.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getOutdatedDevices(): Collection
    {
        return SwitchBotDevice::whereNotIn('serial_number', $this->devices->pluck('serial_number'))
            ->with(['type', 'room', 'hub'])
            ->get();
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> getUpdatedDevices
     * <Function> Get updated devices from scanned results.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getUpdatedDevices(): Collection
    {
        // Normalize serial numbers and map to names
        $switchbotNames = $this->devices->mapWithKeys(function ($device) {
            $serial = strtolower($device['serial_number']);
            return [$serial => $device['name']];
        });

        // Get all matching devices from the DB
        $dbSerials = $switchbotNames->keys();
        $devices = SwitchBotDevice::whereIn('serial_number', $dbSerials)
            ->with(['type', 'room', 'hub'])
            ->get();

        // Attach the original SwitchBot name as 'switchbot_name'
        foreach ($devices as $device) {
            $device->switchbot_name = $switchbotNames[strtolower($device->serial_number)] ?? null;
        }

        return $devices;
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> isResponseValid
     * <Function> Validate the API response and handle exceptions.
     *
     * @param \Illuminate\Http\Client\Response
     * @return bool
     */
    public function isResponseValid(Response $response, ?string $accountName = null): bool
    {
        try {
            throw_unless(
                $response->successful() && $response->json('statusCode') === 100,
                fn() => throw new SynchronizationException($response, $accountName)
            );
        } catch (\Throwable $e) {
            activity()
                ->inLog(Activity::TYPE_ERROR)
                ->withProperties([
                    'Code'   => $e->code,
                    'Action' => $e->action
                ])
                ->log($e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> collectDevices
     * <Function> Collect the devices from the response.
     *
     * @param  \App\Models\Room $room
     * @param  array            $json
     * @return void
     */
    protected function collectDevices(Room $room, array $json): void
    {
        $hubsFromApi = $this->collectHubs($room, $json);

        $devices = collect($json['body']['deviceList'])
            ->filter(function ($item) {
                // collect only items that is included in the TYPES constant in
                // the SwitchBotDevice model class
                return in_array($item['deviceType'], SwitchBotDevice::TYPES);
            })
            ->transform(function ($device) use ($room, $hubsFromApi) {
                // find the type of the device
                $type = $this->types->firstWhere('name', $device['deviceType']);

                // throw error if type is not yet supported
                throw_unless(
                    isset($type),
                    new \Exception(__CLASS__ . ": Device type is not supported")
                );

                // find the hub of the device
                $hub = null;

                // Get hub from api if device is connected to hub
                $hubFromAPI = $hubsFromApi->where('serial_number', Str::lower(@$device['hubDeviceId']))->first();

                // If there is hub, update or create a new record
                if (isset($hubFromAPI)) {
                    $hub = SwitchBotHub::updateOrCreate([
                        'serial_number' => $hubFromAPI['serial_number']
                    ], collect($hubFromAPI)->except('serial_number')->toArray());
                }

                $context = null;

                if (array_key_exists('master', $device)) {
                    $context = [
                        'master' => $device['master'],
                    ];
                }

                // Include curtainDevicesIds if present
                if (isset($device['curtainDevicesIds'])) {
                    $context['curtainDevicesIds'] = $device['curtainDevicesIds'];
                }

                return [
                    'name'           => $device['deviceName'],
                    'serial_number'  => Str::lower($device['deviceId']),
                    'context'        => $context,
                    'room'           => $room,
                    'type'           => $type,
                    'hub'            => $hub,
                ];
            });

        $this->devices = $this->devices->merge($devices);
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> collectHubs
     * <Function> Collect the hubs from the response.
     *
     * @param  array  $json
     * @return \Illuminate\Support\Collection
     */
    protected function collectHubs(Room $room, array $json): Collection
    {
        $hubs = collect($json['body']['deviceList'])
            ->filter(function ($item) {
                // collect only items that is included in the TYPES constant in the
                // SwitchBotHub model class
                return in_array($item['deviceType'], SwitchBotHub::TYPES);
            })
            ->transform(function ($hub) use ($room) {
                // find the type of the remote
                $type = $this->types->firstWhere('name', $hub['deviceType']);

                return [
                    'name'           => $hub['deviceName'],
                    'serial_number'  => Str::lower($hub['deviceId']),
                    'room_id'        => $room->id,
                    'type_id'        => $type->id,
                ];
            });

        return $hubs;
    }

    /**
     * <Layer number> (13.0)
     * 
     * <Processing name> getFailureReason
     * <Function> Get the failure reason from the response.
     *
     * @param mixed $response
     * @return string
     */
    private function getFailureReason(mixed $response): string
    {
        if ($response instanceof ConnectionException) {
            return trans('errors.switch_bot.connection.failed', [
                'message' => $response->getMessage(),
            ]);
        }

        if ($response instanceof Response) {
            return match (true) {
                $response->status() === 401 => trans('errors.switch_bot.account.401'),
                $response->status() === 403 => trans('errors.switch_bot.account.403'),
                $response->status() === 429 => trans('errors.switch_bot.account.429'),
                $response->status() >= 500  => trans('errors.switch_bot.account.500') . ' (' . $response->status() . ')',
                default                     => trans('errors.switch_bot.http.error', ['status' => $response->status()]),
            };
        }

        return trans('errors.switch_bot.unknown_error');
    }
}

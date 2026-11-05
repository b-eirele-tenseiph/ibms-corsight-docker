<?php

namespace App\Services\SwitchBotRemote;

use App\Events\SwitchBot\ResourcesSyncing;
use App\Exceptions\SwitchBot\SynchronizationException;
use App\Models\Activity;
use App\Models\Room;
use App\Models\SwitchBotAccount;
use App\Models\SwitchBotHub;
use App\Models\SwitchBotRemote;
use App\Models\SwitchBotType;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncRemoteService
{
    /**************************************************************************/
    /* Processing hierarchy                                                   */
    /**************************************************************************/
    // RESOURCE_TYPE          (1.0)  The Switch Bot remote resource type.
    // $accounts              (2.0)  The SwitchBotAccount collection.
    // $types                 (3.0)  The SwitchBotType collection.
    // $remotes               (4.0)  The remote collection from each accounts.
    // __construct            (5.0)  The constructor method.
    // handle                 (6.0)  Execute the syncing.
    // collectRemotes         (7.0)  Collect the remotes from the response.
    // getNewRemotes          (8.0)  Get new remotes from scanned results.
    // getOutdatedRemotes     (9.0)  Get outdated remotes from scanned results.
    // getUpdatedRemotes      (10.0) Get updated remotes from scanned results.
    // isResponseValid        (11.0) Validate the API response and handle
    //                               exceptions.
    // collectHubs            (12.0) Collect the hubs from the response.
    // getFailureReason       (13.0) Get the failure reason from the response.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> RESOURCE_TYPE
     * <Function> The Switch Bot remote resource type.
     *
     * @var string
     */
    const RESOURCE_TYPE = 'remote';

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
     * <Processing name> $remotes
     * <Function> The remote collection from each accounts.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $remotes;

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
        ResourcesSyncing::dispatch(self::RESOURCE_TYPE, __('defaults.scan_remote'));

        $responses = Http::pool(function (Pool $pool) {
            return $this->accounts->map(
                fn($account) =>
                $pool->as($account->room_id)
                    ->switchBotPending($account->access_token, $account->secret_key)
                    ->get('/devices')
            )->toArray();
        });

        // Container for scanned remotes
        $this->remotes = collect();

        ResourcesSyncing::dispatch(self::RESOURCE_TYPE, __('defaults.analyze_remote'));

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

            $room->switchBotAccount()->update([
                'last_synced_at' => now()
            ]);

            $this->collectRemotes($room, $response->json());
        }

        $list = $this->remotes;
        $new = $this->getNewRemotes();
        $outdated = $this->getOutdatedRemotes();
        $updated = $this->getUpdatedRemotes();

        return compact('list', 'new', 'outdated', 'updated', 'failed');
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> collectRemotes
     * <Function> Collect the remotes from the response.
     *
     * @param  \App\Models\Room $room
     * @param  array            $json
     * @return void
     */
    protected function collectRemotes(Room $room, array $json): void
    {
        $hubsFromApi = $this->collectHubs($room, $json);

        $remotes = collect($json['body']['infraredRemoteList'])
            ->filter(function ($item) {
                // collect only items that is included in the TYPES constant in
                // the SwitchBotRemote model class
                return in_array($item['remoteType'], SwitchBotRemote::TYPES);
            })
            ->transform(function ($remote) use ($room, $hubsFromApi) {
                // find the type of the remote
                $type = $this->types->firstWhere('name', $remote['remoteType']);

                // throw error if type is not yet supported
                if (!isset($type)) {
                    Log::warning("Unsupported remote type: " . $remote['remoteType']);
                    throw new \Exception(__CLASS__ . ": Remote type is not supported");
                }

                // find the hub of the device
                $hub = null;

                // Get hub from api if device is connected to hub
                $hubFromAPI = $hubsFromApi->where('serial_number', strtolower(@$remote['hubDeviceId']))->first();

                // If there is hub, update or create a new record
                if (isset($hubFromAPI)) {
                    $hub = SwitchBotHub::updateOrCreate([
                        'serial_number' => $hubFromAPI['serial_number']
                    ], collect($hubFromAPI)->except('serial_number')->toArray());
                }

                return [
                    'name'          => $remote['deviceName'],
                    'serial_number' => $remote['deviceId'],
                    'type'          => $type,
                    'hub'           => $hub,
                    'room'          => $room
                ];
            })
            ->reject(function ($item) {
                // Ignore remote where hub is null, it means it has been deleted.
                return !filled($item['hub']);
            });

        $this->remotes = $this->remotes->merge($remotes);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> getNewRemotes
     * <Function> Get new remotes from scanned results.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getNewRemotes(): Collection
    {
        $dbRemotes = SwitchBotRemote::pluck('serial_number')->toArray();

        $newRemotes = array_diff($this->remotes->pluck('serial_number')->toArray(), $dbRemotes);

        return collect($this->remotes->whereIn('serial_number', $newRemotes)->values());
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> getOutdatedRemotes
     * <Function> Get outdated remotes from scanned results.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getOutdatedRemotes(): Collection
    {
        return SwitchBotRemote::whereNotIn('serial_number', $this->remotes->pluck('serial_number'))
            ->with(['hub', 'type', 'room'])
            ->get();
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> getUpdatedRemotes
     * <Function> Get updated remotes from scanned results.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getUpdatedRemotes(): Collection
    {
        // Normalize and map SwitchBot remote serial numbers to names
        $switchbotNames = $this->remotes->mapWithKeys(function ($remote) {
            $serial = strtolower($remote['serial_number']);
            return [$serial => $remote['name']];
        });

        // Fetch existing remotes from DB
        $dbSerials = $switchbotNames->keys();
        $remotes = SwitchBotRemote::whereIn('serial_number', $dbSerials)
            ->with(['hub', 'type', 'room'])
            ->get();

        // Inject switchbot_name for frontend display
        foreach ($remotes as $remote) {
            $remote->switchbot_name = $switchbotNames[strtolower($remote->serial_number)] ?? null;
        }

        return $remotes;
    }

    /**
     * <Layer number> (11.0)
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
                    'serial_number'  => strtolower($hub['deviceId']),
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

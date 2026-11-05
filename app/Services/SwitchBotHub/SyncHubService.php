<?php

namespace App\Services\SwitchBotHub;

use App\Events\SwitchBot\ResourcesSyncing;
use App\Exceptions\SwitchBot\SynchronizationException;
use App\Models\Activity;
use App\Models\Room;
use App\Models\SwitchBotAccount;
use App\Models\SwitchBotHub;
use App\Models\SwitchBotType;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SyncHubService
{
    /**************************************************************************/
    /* Processing hierarchy                                                   */
    /**************************************************************************/
    // RESOURCE_TYPE          (1.0)  The Switch Bot hub resource type.
    // $accounts              (2.0)  The SwitchBotAccount collection.
    // $types                 (3.0)  The SwitchBotType collection.
    // $hubs                  (4.0)  The hubs collection from each accounts.
    // __construct            (5.0)  The constructor method.
    // handle                 (6.0)  Execute the syncing.
    // getNewHubs             (7.0)  Get new hubs from scanned results.
    // getOutdatedHubs        (8.0)  Get outdated hubs from scanned results.
    // getUpdatedHubs         (9.0)  Get existing hubs from scanned results.
    // isResponseValid        (10.0) Validate the API response and handle
    //                               exceptions.
    // collectHubs            (11.0) Collect the hubs from the response.
    // getFailureReason       (12.0) Get the failure reason from the response.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> RESOURCE_TYPE
     * <Function> The Switch Bot hub resource type.
     *
     * @var string
     */
    public const RESOURCE_TYPE = 'hub';

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
     * <Processing name> $hubs
     * <Function> The hubs collection from each accounts.
     *
     * @var \Illuminate\Support\Collection
     */
    protected $hubs;

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

        // Container for scanned hubs
        $this->hubs = collect();
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
        ResourcesSyncing::dispatch(self::RESOURCE_TYPE, __('defaults.scan_hub'));

        // use concurrent requests to retrieve resources on all accounts
        $responses = Http::pool(function (Pool $pool) {
            return $this->accounts->map(
                fn($account) =>
                $pool->as($account->room_id)
                    ->switchBotPending($account->access_token, $account->secret_key)
                    ->get('/devices')
            )->toArray();
        });


        // Dispatch current status => analyzing
        ResourcesSyncing::dispatch(self::RESOURCE_TYPE, __('defaults.analyze_hub'));

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

            // collect hubs from each response
            $this->collectHubs($room, $response->json());
        }

        // Copy of all fetched hubs
        $list = $this->hubs;

        // Store newly fetched hubs here
        $new = $this->getNewHubs();

        // Store outdated or hubs to be removed here
        $outdated = $this->getOutdatedHubs();

        // Store updated hubs here
        $updated = $this->getUpdatedHubs();

        return compact('list', 'new', 'outdated', 'updated', 'failed');
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> getNewHubs
     * <Function> Get new hubs from scanned results.
     *
     * @return array
     */
    private function getNewHubs(): Collection
    {
        $dbHubs = SwitchBotHub::pluck('serial_number')
            ->toArray();

        $newHubs = array_diff($this->hubs->pluck('serial_number')->toArray(), $dbHubs);

        return collect($this->hubs->whereIn('serial_number', $newHubs)->values());
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> getOutdatedHubs
     * <Function> Get outdated hubs from scanned results.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getOutdatedHubs(): Collection
    {
        return SwitchBotHub::whereNotIn('serial_number', $this->hubs->pluck('serial_number'))
            ->with(['type', 'room', 'roomArea'])
            ->get();
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> getUpdatedHubs
     * <Function> Get existing hubs from scanned results.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getUpdatedHubs(): Collection
    {
        // Normalize serial numbers and map to names
        $switchbotNames = $this->hubs->mapWithKeys(function ($hub) {
            $serial = strtolower($hub['serial_number']);
            return [$serial => $hub['name']];
        });

        // Get all matching hubs from the DB
        $dbSerials = $switchbotNames->keys();
        $hubs = SwitchBotHub::whereIn('serial_number', $dbSerials)
            ->with(['type', 'room', 'roomArea'])
            ->get();

        // Attach the original SwitchBot name as 'switchbot_hub'
        foreach ($hubs as $hub) {
            $hub->switchbot_hub = $switchbotNames[strtolower($hub->serial_number)] ?? null;
        }

        return $hubs;
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
     * <Processing name> collectHubs
     * <Function> Collect the hubs from the response.
     *
     * @param  \App\Models\Room $room
     * @param  array            $json
     * @return void
     */
    protected function collectHubs(Room $room, array $json): void
    {
        $hubs = collect($json['body']['deviceList'])
            ->filter(function ($item) {
                // collect only items that is included in the TYPES constant in the
                // SwitchBotHub model class
                return in_array($item['deviceType'], SwitchBotHub::TYPES);
            })
            ->transform(function ($hub) use ($room) {
                // find the type of the hub
                $type = $this->types->firstWhere('name', $hub['deviceType']);

                // throw error if type is not yet supported
                throw_unless(
                    isset($type),
                    new \Exception(__CLASS__ . ": Hub type is not supported")
                );

                return [
                    'name'           => $hub['deviceName'],
                    'serial_number'  => Str::lower($hub['deviceId']),
                    'room'           => $room,
                    'type'           => $type,
                ];
            });

        $this->hubs = $this->hubs->merge($hubs);
    }

    /**
     * <Layer number> (12.0)
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

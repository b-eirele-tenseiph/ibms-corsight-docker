<?php

namespace App\Jobs\Lorawan\AISensor;

use App\Jobs\SwitchBot\SendRemoteCommand;
use App\Models\{DeviceLatestData, SwitchBotRemote};
use Illuminate\Bus\Queueable;
use Illuminate\Support\Collection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\{Cache, Log};
use Throwable;

class ManageAirconsOnPeoplePresence implements ShouldQueue
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $area                (1.0) The associated room ID of the device.
    // $acCommand           (2.0) The command (e.g., "on") for aircon remotes.
    // __construct          (3.0) Create a new job instance and set variables.
    // handle               (4.0) Execute the job logic.
    // failed               (5.0) Handle a job failure.
    // determineCommand     (6.0) Determine command based on presence data.
    // loadRemotesData      (7.0) Get the AC remotes in the target room.
    // sendRemoteCommand    (8.0) Send a command to the specified SwitchBot
    //                            remote.
    // commandDevices       (9.0) Command the AC remotes based on current state.

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $area
     * <Function> The associated room ID of the device.
     *
     * @var string
     */
    private string $area;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $acCommand
     * <Function> The command (e.g., "on") for aircon remotes.
     *
     * @var ?string
     */
    private string $acCommand;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> __construct
     * <Function> Create a new job instance and set variables.
     *
     * @param \App\Models\DeviceLatestData $data
     * @return void
     */
    public function __construct(protected DeviceLatestData $data)
    {
        $this->area = $data->device->room_area_id;
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> handle
     * <Function> Execute the job logic.
     *
     * Evaluates the people count and commands the AC remotes accordingly.
     *
     * @return void
     */
    public function handle(): void
    {
        // getting the latest data must be coming from the device latest data table.
        try {
            $this
                ->determineCommand()
                ->commandDevices();
        } catch (Throwable $exception) {
            $this->fail($exception);
        }
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> failed
     * <Function> Handle a job failure.
     *
     * @param \Throwable  $exception
     * @return void
     */
    public function failed(Throwable $exception): void
    {
        Log::error('Job Failed', [
            'task' => __CLASS__,
            'exception' => [
                'message' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]
        ]);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> determineCommand
     * <Function> Determine command based on presence data.
     *
     * @return self
     */
    private function determineCommand(): self
    {
        $total = $this->data->device
            ->where('room_area_id', $this->area)
            ->whereIn('model', ['VS121', 'VS370'])
            ->with('latestData')
            ->get()
            ->sum(fn($device) => $device->latestData?->context['people_counter']['value'] ?? 0);

        $this->acCommand = $total > 0 ? 'on' : 'off';

        return $this;
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> loadRemotesData
     * <Function> Get the AC remotes in the target area.
     *
     * @return \Illuminate\Support\Collection
     */
    private function loadRemotesData(): Collection
    {
        return SwitchBotRemote::ofType('Air Conditioner')
            ->whereRelation('roomArea', 'room_areas.id', $this->area)
            ->with('latestData')
            ->get();
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> sendRemoteCommand
     * <Function> Send a command to the specified SwitchBot remote.
     *
     * @param mixed $remote
     * @param mixed $data
     * @param int $delay
     * @return void
     */
    private function sendRemoteCommand(mixed $remote, mixed $data, int $delay): void
    {
        $payload = [
            'commandType' => 'command',
            'command' => 'setAll',
            'parameter' => implode(',', $data),
        ];

        SendRemoteCommand::dispatch($remote, $payload, $data)
            ->onConnection('gcloud')
            ->delay(now()->addSeconds($delay));
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> commandDevices
     * <Function> Command the AC remotes based on current state.
     *
     * @return self
     */
    public function commandDevices(): self
    {
        // Send AC command to all registered remote devices
        if ($this->acCommand) {
            $remotes = $this->loadRemotesData();

            foreach ($remotes as $remote) {
                // Get current power state from latest device data
                $context = $remote->latestData?->context ?? [];
                $powerState = $context['powerState'] ?? 'off';

                $defaultConfig = config('settings.sb_ac_default');
                $delay = config('settings.sb_ac_control_delay');
                $cacheKey = "sbremotecontrol-{$remote->id}";

                // Only send command if power state is different from current state
                if ($this->acCommand != $powerState) {
                    Cache::add($cacheKey, $this->acCommand, $delay + 10);

                    $data = array_merge($defaultConfig, $context);
                    $data['powerState'] = $this->acCommand;

                    $this->sendRemoteCommand($remote, $data, $delay);
                } else {
                    // Clear cache if device already in desired state
                    Cache::forget($cacheKey);
                }
            }
        }

        return $this;
    }
}

<?php

namespace App\Jobs\SwitchBot;

use App\Events\SwitchBot\NewSwitchBotDeviceData;
use App\Exceptions\SwitchBot\FetchStatusException;
use App\Models\DeviceLatestData;
use App\Models\SwitchBotDevice;
use App\Models\SwitchBotDeviceData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\Pool;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class FetchDeviceStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $device                (1.0)  The SwitchBotDevice model instance.
    // $responseData          (2.0)  The json-decoded body of the response.
    // $data                  (3.0)  The structured data array.
    // handle                 (4.0)  Execute the action.
    // failed                 (5.0)  Handle a job failure.
    // getDevices             (6.0)  Retrieve all supported SwitchBot devices
    //                               from the database.
    // fetchStatus            (7.0)  Send an API request to fetch the status of
    //                               the SwitchBot device.
    // processResponses       (8.0)  Process the array of responses from the
    //                               HTTP pool.
    // map                    (9.0)  Transform raw payload into structured data.
    // updateLatestDeviceData (10.0) Refresh cached latest device data.
    // persistDataToDatabase  (11.0) Store mapped data in the database.
    // broadcastData          (12.0) Broadcast the data to the client side.
    // prepareDeviceData      (13.0) Prepare device data with all required
    //                               fields.

    private const TYPE_GROUPS = [
        ['Bot'],
        ['Curtain'],
        ['Blind Tilt'],
        ['Keypad', 'Keypad Touch'],
        ['Smart Lock Pro', 'Smart Lock'],
        ['Plug', 'Plug Mini (JP)', 'Plug Mini (US)'],
    ];

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $device
     * <Function> The SwitchBotDevice model instance.
     *
     * @var \App\Models\SwitchBotDevice
     */
    private SwitchBotDevice $device;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $responseData
     * <Function> The json-decoded body of the response.
     *
     * @var array
     */
    private array $responseData;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $data
     * <Function> The structured data array.
     *
     * @var array
     */
    private array $data;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> handle
     * <Function> Execute the action to fetch status for all devices concurrently.
     *
     * @return void
     */
    public function handle(): void
    {
        $devices = $this->getDevices();

        $responses = $this->fetchStatus($devices);

        $this->processResponses($devices, $responses);
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
        // Error handling
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
     * <Processing name> getDevices
     * <Function> Retrieve all supported SwitchBot devices from the database.
     */
    private function getDevices(): Collection
    {
        return collect(self::TYPE_GROUPS)
            ->flatMap(
                fn($group) => SwitchBotDevice::ofType(...$group)
                    ->with(['room.switchBotAccount'])
                    ->get()
            );
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> fetchStatus
     * <Function> Sends concurrent API requests to fetch the status of all devices.
     *
     * @param  \Illuminate\Support\Collection  $devices
     * @return array
     */
    private function fetchStatus(Collection $devices): array
    {
        return Http::pool(function (Pool $pool) use ($devices) {
            $devices->each(function (SwitchBotDevice $device) use ($pool) {
                $pool->as($device->id)
                    ->switchBotPending($device->room->switchBotAccount->access_token, $device->room->switchBotAccount->secret_key)
                    ->timeout(10)
                    ->retry(2, 200)
                    ->get("devices/" . Str::upper($device->serial_number) . "/status");
            });
        });
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> processResponses
     * <Function> Process the array of responses from the HTTP pool.
     */
    private function processResponses(Collection $devices, array $responses): void
    {
        $devicesById = $devices->keyBy('id');

        foreach ($responses as $deviceId => $response) {
            $device = $devicesById[$deviceId];

            if (! $response instanceof \Illuminate\Http\Client\Response) {
                report(new FetchStatusException($response, $device));
                continue;
            }

            if ($response->successful() && $response->json('statusCode') === 100) {
                $this->device = $device;
                $this->responseData = $response->json('body');

                $this
                    ->map()
                    ->updateLatestDeviceData();

                if ($this->device->enable_data_storage) {
                    $this->persistDataToDatabase();
                }

                $this->broadcastData();
            } else {
                report(new FetchStatusException($response, $device));
            }
        }
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> map
     * <Function> Transform raw payload into structured data format.
     *
     * @return $this
     */
    private function map(): self
    {
        $this->data = [
            'via' => SwitchBotDeviceData::VIA_API,
            'received_at' => now(),
            'context' => collect($this->responseData)->except(['deviceId', 'deviceType', 'hubDeviceId'])->all(),
        ];

        return $this;
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> updateLatestDeviceData
     * <Function> Refresh cached latest device data.
     *
     * @return self
     */
    private function updateLatestDeviceData(): self
    {
        DeviceLatestData::updateOrCreate(
            [
                'device_type' => get_class($this->device),
                'device_id' => $this->device->id
            ],
            [
                'context' => $this->data['context'],
                'updated_at' => now()
            ]
        );

        return $this;
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> persistDataToDatabase
     * <Function> Store mapped data in the database.
     *
     * @return void
     */
    private function persistDataToDatabase(): void
    {
        $deviceData = $this->prepareDeviceData();
        $deviceData['context'] = json_encode($deviceData['context']);

        $this->device->data()->insert($deviceData);
        $this->device->updateTimestamps();
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> broadcastData
     * <Function> Broadcast the new device data event.
     *
     * @return void
     */
    private function broadcastData(): void
    {
        $deviceData = $this->prepareDeviceData();

        NewSwitchBotDeviceData::broadcast($this->device, $deviceData);
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> prepareDeviceData
     * <Function> Prepare device data with all required fields.
     *
     * @return array
     */
    private function prepareDeviceData(): array
    {
        return array_merge($this->data, [
            'device_id' => $this->device->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

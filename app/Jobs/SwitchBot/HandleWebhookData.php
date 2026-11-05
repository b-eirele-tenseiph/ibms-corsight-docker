<?php

namespace App\Jobs\SwitchBot;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Events\SwitchBot\NewSwitchBotDeviceData;
use App\Models\DeviceLatestData;
use App\Models\SwitchBotDevice;
use App\Models\SwitchBotDeviceData;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Throwable;

class HandleWebhookData implements ShouldQueue
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $data             (1.0) Holds the transformed device data.
    // __construct       (2.0) Initialize the job with device and payload data.
    // handle            (3.0) Orchestrate the mapping, saving, and broadcasting
    //                         of device data.
    // failed            (4.0) Handle a job failure.
    // map               (5.0) Transform raw payload into structured data format.
    // persistDataTo     (6.0) Store mapped data in the database.
    // Database
    // updateLatest      (7.0) Refresh cached latest device data.
    // DeviceData
    // broadcastData     (8.0) Broadcast the new device data event.
    // prepareDeviceData (9.0) Prepare device data with all required fields.
    // normalizeContext  (10.0)Normalize the context data based on the device
    //                         type.

    use Dispatchable, Queueable;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $data
     * <Function> Holds the transformed device data.
     *
     * @var array
     */
    protected $data;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> __construct
     * <Function> Initialize the job with device and payload data.
     *
     * @param  \App\Models\SwitchBotDevice $device
     * @param  array $payload
     */
    public function __construct(
        protected SwitchBotDevice $device,
        protected array $payload
    ) {}

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> handle
     * <Function> Orchestrate the mapping, saving, and broadcasting of device data.
     *
     * @return void
     */
    public function handle(): void
    {
        $this
            ->map()
            ->updateLatestDeviceData();

        if ($this->device->enable_data_storage) {
            $this->persistDataToDatabase();
        }

        $this->broadcastData();
    }

    /**
     * <Layer number> (4.0)
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
     * <Layer number> (5.0)
     *
     * <Processing name> map
     * <Function> Transform raw payload into structured data format.
     *
     * @return $this
     */
    public function map(): self
    {
        $context = collect($this->payload)
            ->except(['deviceType', 'deviceMac', 'timeOfSample'])
            ->all();

        $context = $this->normalizeContext($context);

        $this->data = [
            'via' => SwitchBotDeviceData::VIA_WEBHOOK,
            'received_at' => Carbon::parse($this->payload['timeOfSample'] / 1000),
            'context' => $context,
        ];

        return $this;
    }

    /**
     * <Layer number> (6.0)
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
     * <Layer number> (7.0)
     *
     * <Processing name> updateLatestDeviceData
     * <Function> Refresh cached latest device data.
     *
     * @return void
     */
    private function updateLatestDeviceData(): void
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
    }

    /**
     * <Layer number> (8.0)
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
     * <Layer number> (9.0)
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

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> normalizeContext
     * <Function> Normalize the context data based on the device type.
     *
     * @param  array  $context
     * @return array
     */
    private function normalizeContext(array $context): array
    {

        $isPlug = str_contains((string) $this->device->device_type, 'Plug')
            || str_contains((string) ($this->payload['deviceType'] ?? ''), 'Plug');

        if (! $isPlug) {
            return $context;
        }

        // Webhook uses powerState; normalize to power
        if (array_key_exists('powerState', $context) && ! array_key_exists('power', $context)) {
            $context['power'] = strtolower((string) $context['powerState']);
            unset($context['powerState']);
        }

        return $context;
    }
}

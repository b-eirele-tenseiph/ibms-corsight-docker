<?php

namespace App\Jobs\Lorawan;

use App\Contracts\Lorawan\PayloadContract as LorawanContract;
use App\Events\Lorawan\NewLorawanDeviceData;
use App\Jobs\Lorawan\Thermostat\MonitorThermostatStatusData;
use App\Models\{DeviceLatestData, LorawanDevice};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

class HandleUplinkDataDefault extends LorawanContract implements ShouldQueue
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct            (1.0)  Initialize job with device and payload data.
    // handle                 (2.0)  Decode payload, process and broadcast data.
    // failed                 (3.0)  Log job failure details.
    // persistDataToDatabase  (4.0)  Insert decoded data into database.
    // updateLatestDeviceData (5.0)  Refresh cached latest device data.
    // consolidateDataWith    (6.0)  Merge new data with existing cache.
    // Existing
    // prepareProcessedData   (7.0)  Add device context to decoded data.
    // Collection
    // broadcastData          (8.0) Dispatch data update event.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Initialize job with device and payload data.
     *
     * @param \App\Models\LorawanDevice $device
     * @param string $payload
     * @return void
     */
    public function __construct(LorawanDevice $device, string $payload)
    {
        $this->device = $device;
        $this->payload = $payload;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> handle
     * <Function> Decode payload, process and broadcast data.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->buildDecoderClass()->decodePayload();

        if ($this->decoded->isNotEmpty()) {

            $this->prepareProcessedDataCollection()->updateLatestDeviceData();

            if ($this->device->enable_data_storage) {
                $this->persistDataToDatabase();

                if ($this->device->classification === 'thermostat') {
                    MonitorThermostatStatusData::dispatch($this->device, $this->dataCollection)
                        ->onConnection('gcloud');
                }
            }

            $this->analyzeThreshold();
            $this->broadcastData();
        }
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> failed
     * <Function> Handle job failure.
     *
     * @param \Throwable $exception
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
     * <Layer number> (4.0)
     *
     * <Processing name> persistDataToDatabase
     * <Function> Insert decoded data into database.
     *
     * @return void
     */
    private function persistDataToDatabase(): void
    {
        $rows = $this->decoded->map(function ($row) {
            $row['context'] = json_encode($row['context']);
            unset($row['room_id']);
            return $row;
        });

        $this->device->data()->insert($rows->all());

        $this->device->touchQuietly();
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> updateLatestDeviceData
     * <Function> Refresh cached latest device data.
     *
     * @return void
     */
    private function updateLatestDeviceData(): void
    {
        if ($this->dataCollection->isEmpty()) {
            return;
        }

        $existingData = DeviceLatestData::where('device_type', LorawanDevice::class)
            ->where('device_id', $this->device->id)
            ->value('context') ?? [];

        $consolidatedData = $this->consolidateDataWithExisting($existingData);

        DeviceLatestData::updateOrCreate(
            [
                'device_type' => LorawanDevice::class,
                'device_id' => $this->device->id
            ],
            [
                'context' => $consolidatedData,
                'updated_at' => now()
            ]
        );
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> consolidateDataWithExisting
     * <Function> Merge new data with existing cache.
     *
     * @param array $existingData
     * @return array
     */
    private function consolidateDataWithExisting(array $existingData): array
    {
        foreach ($this->dataCollection as $dataItem) {
            if (isset($dataItem['type'])) {
                $type = $dataItem['type'];

                // If VS121, save current value as _old before overwriting
                if ($this->device->model === 'VS121' && isset($existingData[$type])) {
                    $existingData[$type . '_old'] = $existingData[$type];
                }

                $existingData[$type] = [
                    'unit'  => $dataItem['context']['unit'] ?? null,
                    'value' => $dataItem['context']['value'] ?? null,
                ];
            }
        }

        return $existingData;
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> prepareProcessedDataCollection
     * <Function> Add device context to decoded data.
     *
     * @return self
     */
    private function prepareProcessedDataCollection(): self
    {
        $this->transformData();

        // Add additional keys using map
        $this->decoded = $this->decoded->map(function ($dataItem) {
            return array_merge($dataItem, [
                'device_id' => $this->device->id,
                'room_id' => $this->device->room_id,
            ]);
        })->values();

        // Keep as arrays instead of converting to objects
        $this->dataCollection = $this->decoded;

        return $this;
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> broadcastData
     * <Function> Broadcast the new data collection.
     *
     * @return self
     */
    private function broadcastData(): void
    {
        NewLorawanDeviceData::broadcast($this->device, $this->dataCollection);
    }
}

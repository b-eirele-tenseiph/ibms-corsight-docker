<?php

namespace App\Observers\Device;

use App\Jobs\Lorawan\AISensor\CountBoothVisitor;
use App\Jobs\Lorawan\AISensor\HandlePeopleCountsInRoom;
use App\Jobs\Lorawan\AISensor\ManageAirconsOnPeoplePresence;
use App\Jobs\Lorawan\AISensor\ProcessOccupancyCount;
use App\Jobs\Lorawan\ElectricMeter\SendCriticalLoadUnbalanceNotification;
use App\Jobs\Monitoring\HandleRoomAreaDeviceCounter;
use App\Models\DeviceLatestData;

class DeviceLatestDataObserver
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // VALID_LORAWAN_MODELS       (1.0)  Define valid LoRaWAN device models for
    //                                   processing.
    // LORAWAN_CONTEXT_KEYS       (2.0)  Define context keys to check for
    //                                   LoRaWAN device validation.
    // SWITCHBOT_REMOTE           (3.0)  Define valid SwitchBot remote
    // _CLASSIFICATIONS                  classifications for processing.
    // created                    (4.0)  Handle the DeviceLatestData "created"
    //                                   event.
    // updated                    (5.0)  Handle the DeviceLatestData "updated"
    //                                   event.
    // processDevice              (6.0)  Process device data and dispatch jobs
    //                                   based on device model and operation.
    // dispatchRegionCounterJobs  (7.0)  Dispatch jobs for processing
    //                                   region-based counts.
    // dispatchLineCounterJobs    (8.0)  Dispatch jobs for processing line
    //                                   crossing counts.
    // dispatchElectricMeterJobs  (9.0)  Dispatch jobs for electric meters.
    // handleRoomAreaCounter      (10.0) Check if device qualifies for room
    //                                   area counter dispatch.
    // isElectricMeter            (11.0) Check if the device is electric meter.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> VALID_LORAWAN_MODELS
     * <Function> Define valid LoRaWAN device models for processing.
     *
     * @var array
     */
    private const VALID_LORAWAN_MODELS = ['WS501', 'WS502', 'WS503', 'UC300'];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> LORAWAN_CONTEXT_KEYS
     * <Function> Define context keys to check for LoRaWAN device validation.
     *
     * @var array
     */
    private const LORAWAN_CONTEXT_KEYS = ['switch_1', 'switch_2', 'switch_3', 'slave_power'];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> SWITCHBOT_REMOTE_CLASSIFICATIONS
     * <Function> Define valid SwitchBot remote classifications for processing.
     *
     * @var array
     */
    private const SWITCHBOT_REMOTE_CLASSIFICATIONS = ['aircon', 'projector', 'television'];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> created
     * <Function> Handle the DeviceLatestData "created" event.
     *
     * @param \App\Models\DeviceLatestData $data
     * @return void
     */
    public function created(DeviceLatestData $data): void
    {
        $this->processDevice($data);

        if ($this->isElectricMeter($data->device)) {
            $this->dispatchElectricMeterJobs($data);
        }
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> updated
     * <Function> Handle the DeviceLatestData "updated" event.
     *
     * @param \App\Models\DeviceLatestData $data
     * @return void
     */
    public function updated(DeviceLatestData $data): void
    {
        if ($data->isDirty('context')) {
            $this->processDevice($data);
        }


        if ($this->isElectricMeter($data->device) || $data->isDirty('context')) {
            $this->dispatchElectricMeterJobs($data);
        }
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> processDevice
     * <Function> Process device data and dispatch jobs based on device model and operation.
     *
     * @param \App\Models\DeviceLatestData $data
     * @return void
     */
    private function processDevice(DeviceLatestData $data): void
    {
        $this->handleRoomAreaCounter($data);

        if ($data->device->model === 'VS121') {
            match ($data->device->context['operation'] ?? null) {
                'region_people_counter' => $this->dispatchRegionCounterJobs($data),
                'line_crossing_counter' => $this->dispatchLineCounterJobs($data),
                default => null
            };
        }
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> dispatchRegionCounterJobs
     * <Function> Dispatch jobs for processing region-based counts.
     *
     * @param \App\Models\DeviceLatestData $data
     * @return void
     */
    private function dispatchRegionCounterJobs(DeviceLatestData $data): void
    {
        HandlePeopleCountsInRoom::dispatch($data)->onConnection('gcloud');
        ManageAirconsOnPeoplePresence::dispatch($data)->onConnection('gcloud');
        CountBoothVisitor::dispatch($data)->onConnection('gcloud');
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> dispatchLineCounterJobs
     * <Function> Dispatch jobs for processing line crossing counts.
     *
     * @param \App\Models\DeviceLatestData $data
     * @return void
     */
    private function dispatchLineCounterJobs(DeviceLatestData $data): void
    {
        ProcessOccupancyCount::dispatch($data)->onConnection('gcloud');
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> dispatchElectricMeterJobs
     * <Function> Dispatch jobs for electric meters.
     *
     * @param \App\Models\DeviceLatestData $data
     * @return void
     */
    private function dispatchElectricMeterJobs(DeviceLatestData $data): void
    {
        SendCriticalLoadUnbalanceNotification::dispatch($data)->onConnection('gcloud');
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> handleRoomAreaCounter
     * <Function> Check if device qualifies for room area counter dispatch.
     *
     * @param \App\Models\DeviceLatestData $data
     * @return void
     */
    private function handleRoomAreaCounter(DeviceLatestData $data): void
    {
        $shouldDispatch = match ($data->device_type) {
            'App\Models\SwitchBotRemote' => in_array($data->device->classification, self::SWITCHBOT_REMOTE_CLASSIFICATIONS),
            'App\Models\SwitchBotDevice' => in_array(
                $data->device->type->name ?? null,
                ['Plug', 'Plug Mini (JP)', 'Plug Mini (US)', 'Bot'],
                true
            ),
            'App\Models\LorawanDevice' => in_array($data->device->model, self::VALID_LORAWAN_MODELS)
                && !empty(array_intersect_key($data->context, array_flip(self::LORAWAN_CONTEXT_KEYS))),
            default => false
        };

        if ($shouldDispatch) {
            HandleRoomAreaDeviceCounter::dispatch($data)->onConnection('gcloud');
        }
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> isElectricMeter
     * <Function> Check if the device is electric meter.
     *
     * @param mixed $device
     * @return bool
     */
    public function isElectricMeter(mixed $device): bool
    {
        $isADW300 = $device->model === 'ADW300';

        $isUC300WithValidSlave = $device->model === 'UC300'
            && in_array($device->context['slave_model'] ?? null, ['EPM5500P', 'ADW300']);

        return $isADW300 || $isUC300WithValidSlave;
    }
}

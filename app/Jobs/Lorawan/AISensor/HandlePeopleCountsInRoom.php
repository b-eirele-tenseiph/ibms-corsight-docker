<?php

namespace App\Jobs\Lorawan\AISensor;

use App\Events\Lorawan\AISensor\RoomPeopleCountUpdated;
use App\Models\DeviceLatestData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;
use Throwable;

class HandlePeopleCountsInRoom implements ShouldQueue
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // DEVICE_MODEL            (1.0) Defines models and associated methods.
    // $dev_eui                (2.0) Stores the device EUI of the LoRaWAN device.
    // $device                 (3.0) The associated LoRaWAN device instance.
    // $payload                (4.0) The payload received from the device.
    // __construct             (5.0) Create a new job instance and set properties.
    // handle                  (6.0) Execute the job logic.
    // failed                  (7.0) Handle job failure.

    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Create a new job instance and set properties.
     *
     * @param \App\Models\DeviceLatestData $device
     * @return void
     */
    public function __construct(
        protected DeviceLatestData $data
    ) {}

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> handle
     * <Function> Execute the job logic.
     *
     * @return void
     */
    public function handle(): void
    {
        $context = $this->data->context;

        if (isset($context['people_counter'])) {
            $this->countPeople();
        }
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> countPeople
     * <Function> Count total people in the room and dispatch event.
     *
     * @return void
     */
    protected function countPeople(): void
    {
        $room = $this->data->device->room;

        $totalPeopleCount = $room
            ->lorawanDevices()
            ->where('model', 'VS121')
            ->where('context->operation', 'region_people_counter')
            ->with('latestData:id,device_id,context')
            ->get()
            ->sum(fn($sensor) => $sensor->latestData?->context['people_counter']['value'] ?? 0);

        RoomPeopleCountUpdated::dispatch([
            'room' => $room->only(['id', 'number', 'name']),
            'people_count' => $totalPeopleCount
        ]);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> failed
     * <Function> Handle a job failure.
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
}

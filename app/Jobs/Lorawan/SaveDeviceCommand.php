<?php

namespace App\Jobs\Lorawan;

use Illuminate\Support\Str;
use App\Events\Lorawan\NewLorawanDeviceData;
use App\Exceptions\Lorawan\CommandException;
use App\Models\LorawanDevice;
use Illuminate\Bus\{Batchable, Queueable};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;
use Throwable;

class SaveDeviceCommand implements ShouldQueue
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $dataCollection  (1.0) The data saved by the model instance.
    // __construct      (2.0) Create a new job instance.
    // handle           (3.0) Execute the job.
    // failed           (4.0) Handle a job failure.
    // saveData         (5.0) Save the created data to database.
    // broadcastData    (6.0) Broadcast the data to the client side.
    // encoder          (7.0) Return the encoder instance based on the device's
    //                        manufacturer and model.
    // prepareData      (8.0) Create data from the given command string from the
    //                        device encoder.

    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $dataCollection
     * <Function> The data saved by the model instance.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    private $dataCollection;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> __construct
     * <Function> Create a new job instance.
     *
     * @param \App\Models\LorawanDevice $device
     * @param array $body
     *
     */
    public function __construct(
        private LorawanDevice $device,
        private array $body
    ) {}

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> handle
     * <Function> Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        if (optional($this->batch())->cancelled()) {
            return;
        }

        $this->saveData()->broadcastData();
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> failed
     * <Function> Handle a job failure.
     *
     * @param \Throwable $exception
     *
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
     * <Processing name> saveData
     * <Function> Save the created data to database.
     *
     * @return self
     */
    private function saveData(): self
    {
        $this->dataCollection = $this->device->data()->createMany($this->prepareData());

        return $this;
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> broadcastData
     * <Function> Broadcast the data to the client side.
     *
     * @return self
     */
    private function broadcastData(): self
    {
        NewLorawanDeviceData::dispatch($this->device, $this->dataCollection);

        return $this;
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> encoder
     * <Function> Return the encoder instance based on the device's manufacturer and model.
     *
     * @return object
     */
    public function encoder(): Object
    {
        $manufacturer = Str::ucfirst($this->device->manufacturer->name);

        // Build the full namespace path for the encoder class
        $encoderClass = "App\\Encoders\\{$manufacturer}\\{$this->device->model}";

        // Throw error if model does not have an encoder class.
        throw_unless(
            class_exists($encoderClass),
            new CommandException([
                'connection' => 'thingpark',
                'code' => 2001,
                'device' => $this->device,
            ])
        );

        return make_class_instance($encoderClass, $this->device);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> prepareData
     * <Function> Create data from the given command string from the device encoder.
     *
     * @return array
     */
    private function prepareData(): array
    {
        return $this->encoder()->encode($this->body)['data'];
    }
}

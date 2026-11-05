<?php

namespace App\Jobs\Lorawan;

use App\Contracts\Lorawan\ControlsContract;
use App\Managers\LorawanManager;
use App\Models\Activity;
use App\Models\LorawanDevice;
use App\Services\ActivityLogger\ActivityLoggerService;
use Illuminate\Bus\{Batchable, Queueable};
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\{InteractsWithQueue, SerializesModels};
use Illuminate\Support\Facades\Log;
use Throwable;

class SendDeviceCommand implements ShouldQueue
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // __construct              (1.0) Create a new job instance.
    // handle                   (2.0) Execute the job.
    // failed                   (3.0) Handle a job failure.
    // getCommandableInstance   (4.0) Get the commandable instance for the
    //                                device.
    // sendCommand              (5.0) Dispatch the command to the device.
    // saveActivityLog          (6.0)  Save the command to activity logs.

    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * <Layer number> (1.0)
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
    ) {
        $this->device = $device->load('gateway');
    }

    /**
     * <Layer number> (2.0)
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

        $this->sendCommand()->saveActivityLog();
    }

    /**
     * <Layer number> (3.0)
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
     * <Layer number> (4.0)
     *
     * <Processing name> getCommandableInstance
     * <Function> Get the commandable instance for the device.
     *
     * @return ControlsContract
     */
    public function getCommandableInstance(): ControlsContract
    {
        return LorawanManager::fromGateway($this->device->gateway)->commander();
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> sendCommand
     * <Function> Dispatch the command to the device.
     *
     * @return self
     */
    private function sendCommand(): self
    {
        $this->getCommandableInstance()
            ->command($this->body)
            ->device($this->device)
            ->send();

        return $this;
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> saveActivityLog
     * <Function> Save the command to activity logs.
     *
     * @return self
     */
    private function saveActivityLog(): self
    {
        ActivityLoggerService::info([
            'code'    => 'I000',
            'message' => get_message(Activity::TYPE_INFO, 'I000', [
                'command' => $this->body['command'],
                'device' => $this->device->name,
            ]),
            'device' =>  $this->device
        ]);

        return $this;
    }
}

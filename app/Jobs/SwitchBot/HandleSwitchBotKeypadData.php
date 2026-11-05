<?php

namespace App\Jobs\SwitchBot;

use App\Models\SwitchBotDevice;
use App\Models\SwitchBotLockAccess;
use App\Services\SwitchBotDevice\SwitchBotKeypad\SwitchBotKeypadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class HandleSwitchBotKeypadData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $device      (1.0) The SwitchBotDevice model instance.
    // $data        (2.0) Additional data for the job.
    // __construct  (3.0) Create a new job instance.
    // handle       (4.0) Execute the job.
    // failed       (5.0) Handle a job failure.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $device
     * <Function> The SwitchBotDevice model instance.
     *
     * @var \App\Models\SwitchBotDevice
     */
    private $device;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $newData
     * <Function> Additional data for the job.
     *
     * @var array
     */
    private $newData;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $sbService
     * <Function> The SwitchBotKeypadService service instance.
     *
     * @var object
     */
    private $sbService;


    /**
     * <Layer number> (3.0)
     *
     * <Processing name> __construct
     * <Function> Create a new job instance.
     *
     * @param  \App\Models\SwitchBotDevice $device
     * @param  array $newData
     * @return void
     */
    public function __construct(SwitchBotDevice $device, array $newData)
    {
        $this->device = $device;
        $this->newData = $newData;
        $this->sbService = new SwitchBotKeypadService();
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> handle
     * <Function> Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->handleLockAccess();
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> failed
     * <Function> Handle a job failure.
     *
     * @param  \Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
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
     * <Processing name> handleLockAccess
     * <Function> Handle the webhook events.
     *
     * @return void
     */
    public function handleLockAccess()
    {
        switch ($this->newData['context']['eventName']) {
            case 'createKey':
                $this->createKey();
                break;
            case 'deleteKey':
                $this->deleteKey();
                break;
            default:
        }
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> createKey
     * <Function> Process the 'createKey' event.
     *
     * @param  \App\Http\Requests\Api\SwitchBot\EndpointRequest  $request
     * @return void
     */
    public function createKey()
    {
        $keyList = $this->sbService->fetchPasscodeList($this->device);

        foreach ($keyList as $key) {
            $sbLockAccess = SwitchBotLockAccess::where('name', $key['name'])
                ->where('status', 'for creation')
                ->first();

            // Check if key status from api is normal
            if ($key['status'] == 'normal') {
                // Update the passcode_id and status of the passcode
                if ($sbLockAccess) {
                    $sbLockAccess->update([
                        'passcode_id' => $key['id'],
                        'status' => 'active',
                        'command_id' => null
                    ]);
                }
            } else {
                // Update status to for deletion if expired
                if ($sbLockAccess) {
                    $sbLockAccess->update([
                        'status' => 'for deletion',
                        'command_id' => null
                    ]);
                }
            }
        }
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> deleteKey
     * <Function> Process the 'deleteKey' event.
     *
     * @param  \App\Http\Requests\Api\SwitchBot\EndpointRequest  $request
     * @return void
     */
    public function deleteKey()
    {
        $passcodeForDeletion = SwitchBotLockAccess::where('status', 'for deletion')
            ->where('command_id', $this->newData['context']['commandId'])
            ->first();

        if ($passcodeForDeletion) {
            $passcodeForDeletion->delete();
        }
    }
}

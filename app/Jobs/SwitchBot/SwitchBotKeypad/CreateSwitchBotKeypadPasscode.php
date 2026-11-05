<?php

namespace App\Jobs\SwitchBot\SwitchBotKeypad;

use App\Models\SwitchBotDevice;
use App\Models\SwitchBotLockAccess;
use App\Services\SwitchBotDevice\SwitchBotKeypad\SwitchBotKeypadService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateSwitchBotKeypadPasscode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // passcodeData              (1.0)  The array representation of the passcode
    //                                  data for api request.
    // $sbLockAccessForCreation  (2.0)  The switch bot lock access model to be
    //                                  created with passcode.
    // $sbService                (3.0)  The SwitchBotKeypadService service
    //                                  instance.
    // $keyList                  (4.0)  The array representation of the passcode
    //                                  list data.
    // __construct               (5.0)  Create a new channel instance.
    // handle                    (6.0)  Execute the job.
    // generatePasscodeData      (7.0)  Generate passcode date based on switch
    //                                  bot lock access.
    // loadKeyList               (8.0)  Get all the passcode list of Keypad 
    //                                  Touch where the status is normal and 
    //                                  name has iBMS.
    // fetchSBLockAccess         (9.0)  Check if there is an access for 
    //                                  creation.
    // createSBLockAccessCommand (10.0) Send an API request for creating key and
    //                                  update switchBot lock access command
    //                                  if successful.
    // failed                    (11.0) Handle a job failure.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> passcodeData
     * <Function> The array representation of the passcode data for api request.
     *
     * @var array
     */
    private $passcodeData;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $sbLockAccessForCreation
     * <Function> The switch bot lock access model to be created with passcode.
     *
     * @var \App\Models\SwitchBotLockAccess
     */
    private $sbLockAccessForCreation;

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
     * <Layer number> (4.0)
     *
     * <Processing name> $keyList
     * <Function> Collection of passcode list data.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    private $keyList;

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> __construct
     * <Function> Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->sbService = new SwitchBotKeypadService();
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> handle
     * <Function> Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->loadKeyList();

        if ($this->fetchSBLockAccess()) {
            $this->generatePasscodeData()
                ->createSBLockAccessCommand();
        }
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> generatePasscodeData
     * <Function> Generate passcode date based on switch bot lock access.
     *
     * @return self
     */
    private function generatePasscodeData(): self
    {
        $this->passcodeData = [
            'commandType' => 'command',
            'command' => 'createKey',
            'parameter' => [
                'name' => $this->sbLockAccessForCreation->name,
                'type' => $this->sbLockAccessForCreation->access_type,
                'password' => $this->sbLockAccessForCreation->access_pin,
            ],
        ];

        switch ($this->sbLockAccessForCreation->access_type) {
            case 'timeLimit':
                $this->passcodeData['parameter'] = array_merge(
                    $this->passcodeData['parameter'],
                    [
                        'startTime' => $this->sbLockAccessForCreation->arrival_time->timestamp,
                        'endTime' => $this->sbLockAccessForCreation->departure_time->timestamp,
                    ]
                );

                break;
            default:
        }

        return $this;
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> loadKeyList
     * <Function> Get all the passcode list of Keypad Touch where the status is
     *            normal and name has iBMS.
     *
     * @return void
     */
    private function loadKeyList(): void
    {
        // Fetch all devices of type Keypad Touch
        $keypads = SwitchBotDevice::ofType('Keypad Touch')->get();

        $keyList = collect([]);

        $keypads->each(function (SwitchBotDevice $keypad) use (&$keyList) {
            $cacheKey = 'keypad_key_list_' . $keypad->id . now()->minute;

            $cachedList = Cache::get($cacheKey);

            $filteredList = collect($cachedList)
                ->where('status', 'normal')
                ->filter(fn($item) => (stripos($item['name'], 'iBMS') !== false));

            $keyList = $keyList->merge(
                $filteredList
            );
        });

        $this->keyList = $keyList;
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> fetchSBLockAccess
     * <Function> Get all record where status is creation and process each.
     *
     * @return boolean
     */
    private function fetchSBLockAccess(): bool
    {
        // Fetch first record with for creation status
        $sbLockAccessForCreation = SwitchBotLockAccess::where('status', 'for creation')
            ->where('retry', '<', 3)
            ->orderBy('updated_at', 'asc')
            ->first();

        while (
            $sbLockAccessForCreation &&
            $this->keyList->contains('name', $sbLockAccessForCreation->name)
        ) {
            $keyFromList = $this
                ->keyList->where('name', $sbLockAccessForCreation->name)
                ->first();

            // Set the status to active if true.
            $sbLockAccessForCreation->update([
                'passcode_id' => $keyFromList['id'],
                'status' => 'active',
                'command_id' => null
            ]);

            // Fetch again SBLock access that status is for creation
            $sbLockAccessForCreation = SwitchBotLockAccess::where('status', 'for creation')
                ->where('retry', '<', 3)
                ->orderBy('updated_at', 'asc')
                ->first();
        }

        $this->sbLockAccessForCreation = $sbLockAccessForCreation;

        return !!$this->sbLockAccessForCreation;
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> createSBLockAccessCommand
     * <Function> Send an API request for creating key and update switchBot lock
     *            access command if successful.
     *
     * @return self
     */
    private function createSBLockAccessCommand(): self
    {
        if ($this->sbLockAccessForCreation) {
            // Update sb lock access with the command id if it was successful
            $createCommand = $this->sbService->createPasscode(
                $this->sbLockAccessForCreation->device,
                $this->passcodeData
            );

            if (isset($createCommand['commandId'])) {
                // Update sb lock access with the command id if it was successful
                $this->sbLockAccessForCreation->update([
                    'command_id' => $createCommand['commandId'],
                    'retry' => 0
                ]);
            } else {
                // Increment the retry count if the command was unsuccessful
                $this->sbLockAccessForCreation->increment('retry');

                //tag the status as failed if the retry is more than and equal to 3
                if ($this->sbLockAccessForCreation->retry >= 3) {
                    $this->sbLockAccessForCreation->update([
                        'status' => 'failed',
                        'command_id' => null,
                        'retry' => 0
                    ]);
                }
            }
        }

        return $this;
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> failed
     * <Function> Handle a job failure.
     *
     * @param  \Throwable  $exception
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
}

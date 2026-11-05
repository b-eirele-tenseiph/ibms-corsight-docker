<?php

namespace App\Jobs\SwitchBot\SwitchBotKeypad;

use App\Models\SwitchBotDevice;
use App\Models\SwitchBotLockAccess;
use App\Services\SwitchBotDevice\SwitchBotKeypad\SwitchBotKeypadService;
use App\Services\SwitchBotDevice\SwitchBotLock\SwitchBotLockAccessService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class DeleteSwitchBotKeypadPasscode implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $passcodesForDeletion      (1.0)  The Switch Bot lock accesses for
    //                                   deletion.
    // $keyList                   (2.0)  The array representation of the 
    //                                   passcode list data.
    // $sbService                 (3.0)  The SwitchBotKeypadService service
    //                                   instance.
    // $sbLockService             (4.0)  The SwitchBotLockAccessService service
    //                                   instance.
    //  __construct               (5.0)  Create a new channel instance.
    // handle                     (6.0)  Execute the job.
    // fetchAllKeyLists           (7.0)  Get all the passcode list of Keypad
    //                                   Touch.
    // deleteAccessesForDeletion  (8.0)  Get all accesses for deletion and
    //                                   process each.
    // deleteExpiredPasscode      (9.0)  Retrieve all expired passcodes and data
    //                                   that do not exist in the database, and
    //                                   delete each one.
    // deletePasscodeAnd          (10.0) Delete the passcode and associated
    // AccessRecord                      SwitchBotLockAccess record.
    // deleteNonexistent          (11.0) Delete the passcode that exists on
    // LockAccesses                      system but not on the passcode list.
    // isKeyNotInDatabase         (12.0) Return whether the name exists in the
    //                                   passcode list in the database.
    // generatePasscodeData       (13.0) Generate command payload for deleteKey
    //                                   request.
    // failed                     (14.0) Handle a job failure.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $passcodesForDeletion
     * <Function> The Switch Bot lock accesses for deletion.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    private $passcodesForDeletion;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $keyList
     * <Function> Collection of passcode list data.
     *
     * @var \Illuminate\Database\Eloquent\Collection
     */
    private $keyList;

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
     * <Processing name> $sbLockService
     * <Function> The SwitchBotLockAccessService service instance.
     *
     * @var object
     */
    private $sbLockService;

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
        $this->sbLockService = new SwitchBotLockAccessService();
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
        // Fetch key list for all devices.
        $this->fetchAllKeyLists();

        // Delete accesses
        $this->deleteAccessesForDeletion();
        $this->deleteNonexistentLockAccesses();
        $this->deleteExpiredPasscode();
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> fetchAllKeyLists
     * <Function> Get all the passcode list of Keypad Touch.
     *
     * @return self
     */
    private function fetchAllKeyLists(): self
    {
        // Fetch all devices of type Keypad Touch.
        SwitchBotDevice::ofType('Keypad Touch')->get()
            ->each(function (SwitchBotDevice $keypad) {
                $cacheKey = 'keypad_key_list_' . $keypad->id . now()->minute;
                // Retrieve or fetch the key list.
                $keyList = Cache::remember($cacheKey, 60, function () use ($keypad) {
                    $fetchedList = $this->sbService->fetchPasscodeList($keypad);

                    if (is_null($fetchedList)) {
                        return null;
                    }

                    return collect($fetchedList)
                        ->filter(fn($item) => (stripos($item['name'], 'iBMS') !== false));
                });

                // Fetch all SwitchBotLockAccess records.
                $lockAccessList = SwitchBotLockAccess::all();

                // Check if the id from $keyList matches any SwitchBotLockAccess entry.
                if ($keyList && $lockAccessList) {
                    $keyList->each(function ($keyItem) use ($lockAccessList) {
                        $matchingLockAccess = $lockAccessList->firstWhere('name', $keyItem['name']);

                        // If the id is found and the status is "failed", update it to "active".
                        if ($matchingLockAccess && $matchingLockAccess->status === 'failed') {
                            $matchingLockAccess->status = 'active';
                            $matchingLockAccess->save(); // Save the updated status.
                        }
                    });
                }

                // Add the key list to the internal array if it is not null.
                if (!is_null($keyList)) {
                    $this->keyList[$keypad->id] = $keyList;
                }
            });

        return $this;
    }


    /**
     * <Layer number> (8.0)
     *
     * <Processing name> deleteAccessesForDeletion
     * <Function> Get all accesses for deletion and process each.
     *
     * @return self
     */
    private function deleteAccessesForDeletion(): self
    {
        // Fetch all switch bot lock access for deletion.
        SwitchBotLockAccess::where('status', 'for deletion')
            ->whereIn('device_id', array_keys(collect($this->keyList)->toArray()))
            ->get()
            ->each(function (SwitchBotLockAccess $sbAccess) {
                // Fetch the key list for the device.
                $isPasscodeInKeyList = $this->keyList[$sbAccess->device_id]->contains('name', $sbAccess->name);

                if ($isPasscodeInKeyList) {
                    // Delete passcode by using service
                    $deleteCommand = $this->sbService->deletePasscode(
                        $sbAccess->device,
                        $this->generatePasscodeData($sbAccess->passcode_id)
                    );
                    // Update sb lock access with the command id if it was successful.
                    $sbAccess->update([
                        'command_id' => @$deleteCommand['commandId']
                    ]);
                } else {
                    // Delete the SwitchBotLockAccess record if it does not exist in the key list.
                    $sbAccess->delete();
                }
            });

        return $this;
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> deleteExpiredPasscode
     * <Function> Retrieve all expired passcodes and data that do not exist
     *            in the database, and delete each one.
     *
     * @return self
     */
    private function deleteExpiredPasscode(): self
    {
        // Delete expired passcodes and passcodes starting with "iBMS" that are not in the database.
        collect($this->keyList)->each(function ($keyList, $keyId) {
            $keyList->each(function ($key) use ($keyId) {
                if ($key['status'] == 'expired' || $this->isKeyNotInDatabase($key['name'])) {
                    // Delete the SwitchBotLockAccess record if it does not exist in the key list.
                    $this->deletePasscodeAndAccessRecord($keyId, $key);
                }
            });
        });

        return $this;
    }


    /**
     * <Layer number> (10.0)
     *
     * <Processing name> deletePasscodeAndAccessRecord
     * <Function> Delete the passcode and associated SwitchBotLockAccess record.
     *
     * @param string $keyId
     * @param \App\Models\SwitchBotLockAccess $key
     * @return self
     */
    private function deletePasscodeAndAccessRecord($keyId, $key): self
    {
        // Fetch the lock device
        $this->sbService->deletePasscode(
            SwitchBotDevice::find($keyId),
            $this->generatePasscodeData($key['id'])
        );

        // Check and delete associated SwitchBotLockAccess record
        $sbLockAccess = SwitchBotLockAccess::where('name', $key['name'])->first();

        if ($sbLockAccess?->user) {
            $this->sbLockService->deleteSwitchBotLockAccess($sbLockAccess->user);
        }

        return $this;
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> deleteNonexistentLockAccesses
     * <Function> Delete the passcode that exists on system but not on the passcode list.
     *
     * @return self
     */
    private function deleteNonexistentLockAccesses(): self
    {
        SwitchBotLockAccess::where('status', 'active')->get()
            ->each(function ($sbAccess) {
                if (
                    isset($this->keyList[$sbAccess->device_id]) &&
                    !$this->keyList[$sbAccess->device_id]->contains('id', $sbAccess->passcode_id)
                ) {
                    $sbAccess->delete();
                }
            });

        return $this;
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> isKeyNotInDatabase
     * <Function> Return whether the name exists in the passcode list in the database.
     *
     * @param string $keyName
     * @return boolean
     */
    private function isKeyNotInDatabase($keyName): bool
    {
        // Check if the key does not exist in the database
        return !SwitchBotLockAccess::where('name', $keyName)->exists();
    }


    /**
     * <Layer number> (13.0)
     *
     * <Processing name> generatePasscodeData
     * <Function> Generate command payload for deleteKey request.
     *
     * @param \App\Models\SwitchBotLockAccess $sbAccess
     * @return array
     */
    private function generatePasscodeData($sbAccess): array
    {
        return [
            'commandType' => 'command',
            'command' => 'deleteKey',
            'parameter' => [
                'id' => $sbAccess,
            ],
        ];
    }

    /**
     * <Layer number> (14.0)
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

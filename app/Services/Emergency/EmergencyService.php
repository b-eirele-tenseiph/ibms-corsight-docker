<?php

namespace App\Services\Emergency;

use App\Jobs\Lorawan\SendDeviceCommand;
use App\Models\Activity;
use App\Models\Admin;
use App\Models\Room;
use App\Traits\Notification\ShouldNotifyAdmin;
use App\Models\LorawanDevice;

class EmergencyService
{
    use ShouldNotifyAdmin;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // ERROR_CODE      (1.0)  The error code reference for getting the message.
    // $message        (2.0)  The message based on the given message id.
    // $action         (3.0)  The action based on the given error id describing
    //                        how to resolve it.
    // $activityLog    (4.0)  The activity log instance.
    // $trigger        (5.0)  The trigger origin of the emergency whether its
    //                        on ibms screen or device.
    // $command        (6.0)  Command that will use to command the siren.
    // dispatch        (7.0)  Main entry of dispatching the emergency alarm.
    // saveLog         (8.0)  Save the emergency log with its details.
    // notifiables     (9.0)  The models with notifiable traits.
    // notification    (10.0)  The notification data.
    // prepareMessage  (11.0) Retrieve message from message list using id
    //                        depending on which type of access was recorded.
    // getRoom         (12.0) Retrieve the room of the user or the device
    //                        trigger.
    // activateSiren   (13.0) Sends command to activate the siren.
    // turnOffSiren    (14.0) Turning of sirens in a room

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> ERROR_CODE
     * <Function> The error code reference for getting the message.
     *
     * @var String
     */
    const ERROR_CODE = 'E011';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $message
     * <Function> The message based on the given message id.
     *
     * @var String
     */
    private $message;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $action
     * <Function> The action based on the given error id describing how to
     *            resolve it.
     *
     * @var String
     */
    private $action;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $activityLog
     * <Function> The activity log instance.
     *
     * @var \App\Models\Activity
     */
    private Activity $logInfo;

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> $trigger
     * <Function> The trigger origin of the emergency whether its on ibms screen
     *            or device.
     *
     * @var \App\Models\User|\App\Models\LorawanDevice
     */
    private $trigger;

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> $command
     * <Function> Command that will use to command the siren.
     *
     * @var \App\Models\User|\App\Models\LorawanDevice
     */
    private $command;

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> dispatch
     * <Function> Main entry of dispatching the emergency alarm.
     *
     * @param  \App\Models\User|\App\Models\LorawanDevice $trigger
     * @return void
     */
    public function dispatch($trigger, string $command)
    {
        $this->trigger = $trigger;
        $this->command = $command;

        $this->prepareMessage()
            ->activateSiren()
            ->saveLog()
            ->sendAdminNotification();
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> saveLog
     * <Function> Save the emergency log with its details.
     *
     * @return self
     */
    public function saveLog(): self
    {
        $this->logInfo = activity()
            ->inLog(Activity::TYPE_ERROR)
            ->withProperties([
                'Code'    => self::ERROR_CODE,
                'Floor'   => optional($this->getRoom()->floor)->name ?? '-not-set-',
                'Room'    => $this->getRoom()->name,
                'Action'  => $this->action,
                'room_id' => $this->getRoom()->id,
            ])
            ->log($this->message);

        return $this;
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> notifiables
     * <Function> The models with notifiable traits.
     *
     * @return void
     */
    public function notifiables()
    {
        return Admin::whereHas('roles', function ($query) {
            $query->whereIn('name', ['staff', 'admin', 'super admin']);
        })->get();
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> notification
     * <Function> The notification data.
     *
     * @return array
     */
    protected function notification(): array
    {
        return [
            'nature' => Activity::TYPE_ERROR,
            'title' => 'Emergency',
            'message' => $this->message,
            'url' => route('admin.management.logs.index', [
                'search'    => $this->logInfo->id,
            ])
        ];
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> prepareMessage
     * <Function> Retrieve message from message list using id depending
     *            on which type of access was recorded.
     *
     * @return self
     */
    private function prepareMessage(): self
    {
        // Retrieve message using helper.
        $this->message = get_message(Activity::TYPE_ERROR, self::ERROR_CODE, ['room' => $this->getRoom()->name]);

        // Prepare action message.
        $this->action = get_action(Activity::TYPE_ERROR, self::ERROR_CODE);

        return $this;
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> getRoom
     * <Function> Retrieve the room of the user or the device trigger.
     *
     * @return \App\Models\Room
     */
    private function getRoom(): Room
    {
        return $this->trigger->room;
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> activateSiren
     * <Function> Sends command to activate the siren.
     *
     * @return self
     */
    private function activateSiren(): self
    {
        // Retrieve all Siren devices (R602B model)
        $sirens = LorawanDevice::deviceType('siren')
            ->where('room_id', $this->getRoom()->id)
            ->get();

        foreach ($sirens as $siren) {
            SendDeviceCommand::dispatch($siren, ["command" => $this->command]);
        }
        return $this;
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> turnOffSiren
     * <Function> Turning of sirens in a room
     *
     * @param  string $roomId
     * @return void
     */
    static function turnOffSiren($roomId)
    {
        // Retrieve all Siren devices (R602B model)
        $sirens = LorawanDevice::deviceType('siren')
            ->where('room_id', $roomId)
            ->get();

        foreach ($sirens as $siren) {
            SendDeviceCommand::dispatch($siren, ["command" => 'MuteMode']);
        }
    }
}

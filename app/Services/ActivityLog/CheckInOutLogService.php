<?php

namespace App\Services\ActivityLog;

use App\Models\Activity;
use App\Models\Room;
use App\Models\User;
use App\Traits\Notification\ShouldNotifyAdmin;

class CheckInOutLogService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $user                 (1.0) Declare user model to be used on creating
    //                             activity log and for notifying admin
    //                             thereafter
    // $type                 (2.0) Action type identifier (check_in or
    //                             check_out).
    // $message              (3.0) Activity message based on message ID.
    // $activityLog          (4.0) The activity log instance.
    // handleCheckInOut      (5.0) Main entry for processing check-in/out
    //                             and logging.
    // saveActivityLog       (6.0) Save the activity log to the database.
    // notification          (7.0) Build notification data (via trait).
    // getRoom               (8.0) Retrieve the user's assigned room.
    // prepareMessage        (9.0) Prepares the message based on type and
    //                             room name.

    use ShouldNotifyAdmin;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $user
     * <Function> The user performing the check-in/out.
     *
     * @var App\Models\User;
     */
    private $user;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $type
     * <Function> Action type identifier (check_in or check_out).
     *
     * @var String
     */
    private $type;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $message
     * <Function> Activity message based on message ID.
     *
     * @var String
     */
    private $message;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $activityLog
     * <Function> Activity log instance.
     *
     * @var \Spatie\Activitylog\Models\Activity
     */
    private $activityLog;

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> handleCheckInOut
     * <Function> Main entry for processing check-in/out and logging.
     *
     * @param  string           $type
     * @param  \App\Models\User $user
     * @return void
     */
    public function handleCheckInOut(string $type, User $user): void
    {
        // Retrieve user who logged in.
        $this->user = $user;

        // Retrieve message id depending on the log type
        $this->type = $type === 'in' ? 'I100' : 'I101';

        // Process data chronologically
        $this->prepareMessage()
            ->saveActivityLog()
            ->sendAdminNotification();
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> saveActivityLog
     * <Function> Save the activity log to the database.
     *
     * @return self
     */
    public function saveActivityLog(): self
    {

        $this->activityLog = activity()
            ->inLog(Activity::TYPE_INFO)
            ->withProperties([
                'Code'    => $this->type,
                'Floor'   => optional($this->getRoom()->floor)->name ?? '-not-set-',
                'Room'    => $this->getRoom()->name
            ])
            ->log($this->message);

        return $this;
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> notification
     * <Function> Build notification data (via trait).
     *
     */
    public function notification(): array
    {
        return [
            'nature' => Activity::TYPE_INFO,
            'title' => __('notification.check_in_out.title'),
            'message' => $this->message,
            'url' => route('admin.management.logs.index', [
                'search'    => $this->activityLog->id,
                'severity'  => 'information'
            ])
        ];
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> getRoom
     * <Function> Retrieve the user's assigned room.
     *
     * @return \App\Models\Room
     */
    private function getRoom(): Room
    {
        return $this->user->room;
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> prepareMessage
     * <Function> Prepares the message based on type and room name.
     *
     * @return self
     */
    private function prepareMessage(): self
    {
        // Retrieve message using helper.
        $this->message = get_message(Activity::TYPE_INFO, $this->type, ['room' => $this->getRoom()->name]);

        return $this;
    }
}

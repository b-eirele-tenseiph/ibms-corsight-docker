<?php

namespace App\Services\ActivityLog;

use App\Models\Activity;
use App\Models\Scene;
use App\Models\User;
use App\Traits\Notification\ShouldNotifyAdmin;
use App\Traits\Notification\ShouldNotifyUser;
use Illuminate\Support\Collection;

class SceneExecutionLogService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $scene                (1.0) The scene that was executed.
    // $users                (2.0) The users in the room associated with the
    //                             scene.
    // $AdminMessage         (3.0) Activity message based on message ID.
    // $userMessage          (4.0) User activity message based on message ID.
    // $activityLog          (5.0) The activity log instance.
    // handle                (6.0) Main entry for processing scene execution
    //                             logging and notifications.
    // saveActivityLog       (7.0) Save the activity log to the database.
    // notification          (8.0) Build admin notification data (via trait).
    // userNotification      (9.0) Build user notification data (via trait).
    // notifiableUser        (10.0) Retrieve the users to notify.
    // prepareMessage        (11.0) Prepares the message based on scene and
    //                              room name.

    use ShouldNotifyAdmin, ShouldNotifyUser;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $scene
     * <Function> The scene that was executed.
     *
     * @var \App\Models\Scene
     */
    private Scene $scene;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $users
     * <Function> The users in the room associated with the scene.
     *
     * @var \Illuminate\Support\Collection
     */
    private Collection $users;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $AdminMessage
     * <Function> Admin activity message based on message ID.
     *
     * @var string
     */
    private string $AdminMessage;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $userMessage
     * <Function> User activity message based on message ID.
     *
     * @var string
     */
    private string $userMessage;

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> $activityLog
     * <Function> Activity log instance.
     *
     * @var \Spatie\Activitylog\Models\Activity
     */
    private $activityLog;

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> handle
     * <Function> Main entry for processing scene execution logging and
     *            notifications.
     *
     * @param  \App\Models\Scene $scene
     * @return void
     */
    public function handle(Scene $scene): void
    {
        $this->scene = $scene;

        $this->users = User::whereHas('roomAccess', function ($q) use ($scene) {
            $q->where('room_id', $scene->room_id);
        })->get();

        $this->prepareMessage()
            ->saveActivityLog()
            ->sendAdminNotification();

        if ($this->users->isNotEmpty()) {
            $this->sendUserNotification();
        }
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> saveActivityLog
     * <Function> Save the activity log to the database.
     *
     * @return self
     */
    private function saveActivityLog(): self
    {
        $this->activityLog = activity()
            ->inLog(Activity::TYPE_INFO)
            ->withProperties([
                'Code'  => 'I006',
                'Room'  => $this->scene->room->name,
                'Scene' => $this->scene->name,
            ])
            ->log($this->AdminMessage);

        return $this;
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> notification
     * <Function> Build admin notification data (via trait).
     *
     * @return array
     */
    protected function notification(): array
    {
        return [
            'nature'  => Activity::TYPE_INFO,
            'title'   => __('notification.scene.title'),
            'message' => $this->AdminMessage,
            'url'     => route('admin.management.logs.index', [
                'search'   => $this->activityLog->id,
                'severity' => 'information',
            ]),
        ];
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> userNotification
     * <Function> Build user notification data (via trait).
     *
     * @return array
     */
    protected function userNotification(): array
    {
        return [
            'nature'  => Activity::TYPE_INFO,
            'title'   => __('notification.scene.title'),
            'message' => $this->userMessage,
            'url'     => null,
        ];
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> notifiableUser
     * <Function> Retrieve the users to notify.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function notifiableUser(): Collection
    {
        return $this->users;
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> prepareMessage
     * <Function> Prepares the message based on scene and room name.
     *
     * @return self
     */
    private function prepareMessage(): self
    {
        $this->AdminMessage = get_message(Activity::TYPE_INFO, 'I006', [
            'scene' => $this->scene->name,
            'room'  => $this->scene->room->name,
        ]);

        $this->userMessage = get_message(Activity::TYPE_INFO, 'I007', [
            'scene' => $this->scene->name,
        ]);

        return $this;
    }
}

<?php

namespace App\Traits\Notification;

use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Notification;

trait ShouldNotifyUser
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // userNotification      (1.0) An array of notification data.
    // sendUserNotification  (2.0) Initiate notification sending to the user.
    // notifiableUser        (3.0) The user model to notify.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> userNotification
     * <Function> An array of notification data.
     *
     * @return array
     */
    abstract protected function userNotification(): array;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> sendUserNotification
     * <Function> Initiate notification sending to the user.
     *
     * @return void
     */
    public function sendUserNotification(): void
    {
        Notification::send($this->notifiableUser(), new UserNotification($this->userNotification()));
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> notifiableUser
     * <Function> The user model to notify.
     *
     * @return mixed
     */
    abstract protected function notifiableUser(): mixed;
}

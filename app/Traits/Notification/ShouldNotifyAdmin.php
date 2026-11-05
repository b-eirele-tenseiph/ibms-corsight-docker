<?php

namespace App\Traits\Notification;

use App\Models\Admin;
use App\Notifications\AdminNotification;
use Illuminate\Support\Facades\Notification;

trait ShouldNotifyAdmin
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // notification           (1.0) An array of notification data.
    // sendAdminNotification  (2.0) Initiate notification sending to all admin
    //                              or selected notifiables.
    // notifiables            (3.0) The models with notifiable traits.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> notification
     * <Function> An array of notification data.
     *
     * @return array
     */
    abstract protected function notification(): array;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> sendAdminNotification
     * <Function> Initiate notification sending to all admin or selected
     *            notifiables
     *
     * @return void
     */
    public function sendAdminNotification()
    {
        Notification::send($this->notifiables(), new AdminNotification($this->notification()));
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> notifiables
     * <Function> The models with notifiable traits.
     *
     * @return void
     */
    public function notifiables()
    {
        return Admin::all();
    }
}

<?php

namespace App\Observers;

use App\Models\Activity;
use App\Models\Promo;
use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class PromoObserver
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // created        (1.0) Fired after an Promo is created; notifies users.
    // updated        (2.0) Fired after an Promo is updated; notifies users
    //                      only when meaningful fields changed.
    // notifyUsers    (3.0) Dispatch notification to Users with permission
    //                      after DB transaction commits.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> created
     * <Function> Fired after an Promo is created; notifies users.
     *
     * @param  \App\Models\Promo  $promo
     * @return void
     */
    public function created(Promo $promo): void
    {
        $this->notifyUsers('title_add', $promo);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> updated
     * <Function> Fired after an Promo is updated; notifies users only when
     *            meaningful fields changed.
     *
     * @param  \App\Models\Promo  $promo
     * @return void
     */
    public function updated(Promo $promo): void
    {
        // Only notify when meaningful fields change (tweak as needed)
        if (! $promo->wasChanged(['title', 'description', 'valid_start_date', 'Valid_end_date'])) {
            return;
        }

        $this->notifyUsers('title_update', $promo);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> notifyUsers
     * <Function> Dispatch notification to Users with 'access promos'
     *            permission AFTER the database transaction commits to prevent
     *            race conditions with queued/broadcast notifications.
     *
     * @param  string                 $titleKey
     * @param  \App\Models\Promo    $promo
     * @return void
     */
    protected function notifyUsers(string $titleKey, Promo $promo): void
    {
        // Since service wraps ops in DB::transaction(), run after commit
        DB::afterCommit(function () use ($titleKey, $promo) {
            Notification::send(
                User::permission('access promos')->get(),
                new UserNotification([
                    'nature' => Activity::TYPE_INFO,
                    'title'   => __('notification.promos.' . $titleKey),
                    'message' => $promo->description ?: $promo->title,
                    'url' => route('user.promos.index')
                ])
            );
        });
    }
}

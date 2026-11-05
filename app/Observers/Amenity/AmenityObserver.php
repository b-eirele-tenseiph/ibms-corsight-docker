<?php

namespace App\Observers\Amenity;

use App\Events\Amenity\AmenityVisibility;
use App\Models\Amenity;
use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use App\Models\Activity;

class AmenityObserver
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // created        (1.0) Fired after an Amenity is created; notifies users.
    // updated        (2.0) Fired after an Amenity is updated; notifies users
    //                      only when meaningful fields changed.
    // notifyUsers    (3.0) Dispatch notification to Users with permission
    //                      after DB transaction commits.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> created
     * <Function> Fired after an Amenity is created; notifies users.
     *
     * @param  \App\Models\Amenity  $amenity
     * @return void
     */
    public function created(Amenity $amenity): void
    {
        $this->notifyUsers('title_add', $amenity);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> updated
     * <Function> Fired after an Amenity is updated; notifies users only when
     *            meaningful fields changed.
     *
     * @param  \App\Models\Amenity  $amenity
     * @return void
     */
    public function updated(Amenity $amenity): void
    {
        // Check if visibility changed
        if ($amenity->wasChanged('is_visible')) {
            event(new AmenityVisibility($amenity->id, $amenity->is_visible));
        }

        // Notify user if amenity is visible and properties are updated
        if ($amenity->is_visible && $amenity->wasChanged(['title', 'description', 'category_id', 'attributes'])) {
            $this->notifyUsers('title_update', $amenity);
        }
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> notifyUsers
     * <Function> Dispatch notification to Users with 'access amenities'
     *            permission AFTER the database transaction commits to prevent
     *            race conditions with queued/broadcast notifications.
     *
     * @param  string                 $titleKey
     * @param  \App\Models\Amenity    $amenity
     * @return void
     */
    protected function notifyUsers(string $titleKey, Amenity $amenity): void
    {
        // Since service wraps ops in DB::transaction(), run after commit
        DB::afterCommit(function () use ($titleKey, $amenity) {
            Notification::send(
                User::permission('access amenities')->get(),
                new UserNotification([
                    'nature' => Activity::TYPE_INFO,
                    'type'    => __('notification.amenity'),
                    'title'   => __('notification.amenities.' . $titleKey),
                    'content' => $amenity->description ?: $amenity->title,
                    'url'     => route('user.amenities.index'),
                ])
            );
        });
    }
}

<?php

namespace App\Observers\SwitchBotLock;

use App\Events\SwitchBotLockAccess\ManageSwitchBotLockStatus;
use App\Models\Activity;
use App\Models\SwitchBotLockAccess;

class SwitchBotLockAccessDataObserver
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // created        (1.0) Handle the SwitchBotLockAccess "created" event.
    // updated        (2.0) Handle the SwitchBotLockAccess "updated" event.
    // deleted        (3.0) Handle the SwitchBotLockAccess "deleted" event.
    // restored       (4.0) Handle the SwitchBotLockAccess "restored" event.
    // forceDeleted   (5.0) Handle the SwitchBotLockAccess "force deleted" event.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> created
     * <Function> Handle the SwitchBotLockAccess "created" event.
     *
     * @param  \App\Models\SwitchBotLockAccess  $switchBotLockAccess
     * @return void
     */
    public function created(SwitchBotLockAccess $switchBotLockAccess)
    {
        ManageSwitchBotLockStatus::dispatch($switchBotLockAccess);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> updated
     * <Function> Handle the SwitchBotLockAccess "updated" event.
     *
     * @param  \App\Models\SwitchBotLockAccess  $switchBotLockAccess
     * @return void
     */
    public function updated(SwitchBotLockAccess $switchBotLockAccess)
    {
        if ($switchBotLockAccess->status != 'for deletion') {
            ManageSwitchBotLockStatus::dispatch($switchBotLockAccess);
        }
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> deleted
     * <Function> Handle the SwitchBotLockAccess "deleted" event.
     *
     * @param  \App\Models\SwitchBotLockAccess  $switchBotLockAccess
     * @return void
     */
    public function deleted(SwitchBotLockAccess $switchBotLockAccess)
    {
        ManageSwitchBotLockStatus::dispatch($switchBotLockAccess);
        activity()
            ->inLog(Activity::TYPE_INFO)
            ->withProperties([
                'code'    => 'I002',
                'floor'   => optional($switchBotLockAccess->device->room->floor)->name ?? '-not-set-',
                'room'    => $switchBotLockAccess->device->room->name,
                'user'    => $switchBotLockAccess->name
            ])
            ->log(get_message(Activity::TYPE_INFO, 'I002', ['room' => $switchBotLockAccess->device->room->name]));
    }
}

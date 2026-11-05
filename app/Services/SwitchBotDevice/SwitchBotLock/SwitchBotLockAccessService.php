<?php

namespace App\Services\SwitchBotDevice\SwitchBotLock;

use App\Models\SwitchBotDevice;
use App\Models\User;

class SwitchBotLockAccessService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // createSwitchBotLockAccess    (1.0) Create switch bot lock access entry
    //                                     for user using data.
    // deleteSwitchBotLockAccess    (2.0) Change status of switch bot lock
    //                                     accesses of user to for deletion.
    // updateSwitchBotLockAccess    (3.0) Delete previous and create new switch
    //                                     bot lock accesses for user.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> createSwitchBotLockAccess
     * <Function> Create switch bot lock access entry for user using data.
     *
     * @param  \App\Models\User $user
     * @param  object $device
     * @param  array $data
     * @return void
     */
    public function createSwitchBotLockAccess(User $user, $device, array $data): void
    {
        $device->each(function (SwitchBotDevice $keypad) use (&$user, $data) {
            $user->switchBotLockAccess()->create(array_merge($data, [
                'device_id' => $keypad->id,
            ]));
        });
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> deleteSwitchBotLockAccess
     * <Function> Change status of switch bot lock accesses of user to for
     *            deletion.
     *
     * @param  \App\Models\User $user
     * @return void
     */
    public function deleteSwitchBotLockAccess(User $user)
    {
        // Update status to for deletion of all accesses
        $user->switchBotLockAccess()->update([
            'status' => 'for deletion'
        ]);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> updateSwitchBotLockAccess
     * <Function> Delete previous and create new switch bot lock accesses for
     *            user.
     *
     * @param  \App\Models\User $user
     * @param  object $device
     * @param  array $data
     * @return void
     */
    public function updateSwitchBotLockAccess(User $user, $device, array $data)
    {
        // Delete previous accesses
        $this->deleteSwitchBotLockAccess($user);

        // Create new accesses
        $this->createSwitchBotLockAccess($user, $device, $data);
    }
}

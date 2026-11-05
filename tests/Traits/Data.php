<?php

namespace Tests\Traits;

use App\Models\Admin;
use App\Models\Room;
use Illuminate\Database\Eloquent\Model;
use App\Models\Activity;
use App\Models\SwitchBotDevice;
use App\Models\LorawanDeviceData;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

trait Data
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // createAdminUser            (1.0) Instantiate a factory instance for Admin
    // createRoom                 (2.0) Instantiate a factory instance for Room.
    // createMultipleAdminUsers   (3.0) Instantiate 20 instances using the
    //                                  factory for the Admin
    // createActivity             (4.0) Create new Activity log.
    // createAdminWithRole        (5.0) Creation of an admin user with specific
    //                                  roles.
    // createUserWithPermission   (6.0) Creation of a user with permission.
    // createSwitchBotDevice      (7.0) Creation of switchbot device with room 
    //                                  and switchbot account.
    // createLorawanDeviceData    (8.0) Instantiate a factory instance for 
    //                                  LorawanDeviceData
  
    /**
     * <Layer number> (1.0)
     *
     * <Processing name> createAdminUser
     * <Function> Instantiate a factory instance for Admin.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function createAdminUser(): Model
    {
        return Admin::factory()
            ->state([
                'password' => Hash::make('password'),
                'enabled' => true,
            ])
            ->createOne();
    }
    /**
     * <Layer number> (2.0)
     *
     * <Processing name> createRoom
     * <Function> Instantiate a factory instance for Room.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function createRoom(): Model
    {
        return Room::factory()->create();
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> createMultipleAdminUsers
     * <Function> Instantiate 20 instances using the factory for the Admin
     *
     * @return \Illuminate\Support\Collection
     */
    protected function createMultipleAdminUsers()
    {
        return Admin::factory()
            ->count(20)
            ->state(['password' => bcrypt('password')])
            ->create();
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> createActivity
     * <Function> Create new Activity log.
     *
     * @return \App\Models\Activity
     */
    protected function createActivity()
    {
        return Activity::factory()
            ->count(50)
            ->create();
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> createAdminWithRole
     * <Function> Creation of an admin user with specific roles
     *
     * @return model
     */
    private function createAdminWithRole($name, $username, $password, $roles)
    {
        $admin = Admin::factory()->state([
            'name' => $name,
            'username' => $username,
            'enabled' => true,
            'password' => Hash::make($password)
        ])->create();

        foreach ($roles as $role) {
            $admin->assignRole($role);
        }

        return $admin;
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> createUserWithPermission
     * <Function> Creation of a user with permission
     *
     * @return void
     */
    private function createUserWithPermission($permissionName, $guardName = 'web')
    {
        Permission::create([
            'name' => $permissionName,
            'guard_name' => $guardName,
        ]);

        $user = User::factory()->create();
        $user->givePermissionTo($permissionName);

        return $user;
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> createSwitchBotDevice
     * <Function> Creation of switchbot device with room and switchbot account.
     *
     * @return model
     */
    private function createSwitchBotDevice()
    {
        $device = SwitchBotDevice::factory()->create();

        return $device;
    }
  
    /**
     * <Layer number> (8.0)
     *
     * <Processing name> createLorawanDeviceData
     * <Function> Instantiate a factory instance for LorawanDeviceData
     *
     * @return \App\Models\LorawanDeviceData
     */
    protected function createLorawanDeviceData(): LorawanDeviceData
    {
        return LorawanDeviceData::factory()
            ->create();

    }
}

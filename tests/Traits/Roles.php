<?php

namespace Tests\Traits;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\Traits\Permissions;

trait Roles
{
    use Permissions;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // createSARole                     (1.0) Creation of super admin role
    // createAdminRole                  (2.0) Creation of admin role
    // createStaffRole                  (3.0) Creation of staff role
    // createGuestRole                  (4.0) Creation of guest role

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> createSARole
     * <Function> Creation of super admin role
     *
     * @return void
     */
    protected function createSARole()
    {

        $superAdmin = Role::create([
            'name' => 'super admin',
            'guard_name' => 'admin'
        ]);

        $superAdmin->givePermissionTo(
            Permission::where('guard_name', 'admin')->get(),
        );
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> createAdminRole
     * <Function> Creation of admin role
     *
     * @return void
     */
    protected function createAdminRole()
    {
        $admin = Role::create([
            'name' => 'admin',
            'guard_name' => 'admin'
        ]);

        $admin->givePermissionTo(
            Permission::whereIn('name', [
                'monitor devices',
                'manage rooms',
                'manage lorawan',
                'manage switchbot',
                'manage corsight',
                'manage logs',
                'manage remotelock',
                'manage general settings',
                'manage lorawan devices',
            ])->where('guard_name', 'admin')->get()
        );
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> createStaffRole
     * <Function> Creation of staff role
     *
     * @return void
     */
    protected function createStaffRole()
    {
        $staff = Role::create([
            'name' => 'staff',
            'guard_name' => 'admin'
        ]);

        $staff->givePermissionTo(
            Permission::whereIn('name', [
                'monitor devices',
                'manage users',
                'access users',
                'manage logs',
                'manage rooms',
                'manage room access',
                'manage promos',
                'manage amenities',
                'manage lorawan devices'
            ])->where('guard_name', 'admin')->get()
        );

        $staff->givePermissionTo('control devices')->where('guard_name', 'web');
    }


    /**
     * <Layer number> (4.0)
     *
     * <Processing name> createUserRole
     * <Function> Creation of user role for web guard
     *
     * @return void
     */
    protected function createUserRole()
    {
        $user = Role::create([
            'name' => 'user',
            'guard_name' => 'web'
        ]);

        $user->givePermissionTo(
            Permission::whereIn('name', [
                'control devices',
                'access amenities',
            ])->where('guard_name', 'web')->get()
        );
    }
}

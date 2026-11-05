<?php

namespace Tests\Traits;

use App\Models\User;
use Spatie\Permission\Models\Permission;

trait Permissions
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // createPermissions               (1.0) Creating a list of permissions
    // giveAdminPermissionTo           (2.0) Assigning all admin permissions
    //                                       to a user

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> createPermissions
     * <Function> Creating a list of permissions
     *
     * @return \Spatie\Permission\Models\Permission
     */
    protected function createPermissions(): void
    {
        // all permissions with guard name 'admin'
        $arrayOfAdminPermissions = [
            'monitor devices',
            'control devices',
            'manage rooms',
            'access admins',
            'access users',
            'manage room access',
            'manage general settings',
            'manage roles',
            'manage switchbot',
            'manage corsight',
            'manage lorawan',
            'manage lorawan devices',
            'manage user accounts',
            'manage logs',
            'manage amenities',
            'room monitoring',
            'electric monitoring',
            'manage orders',
            'manage scenes'
        ];

        // all permissions with guard name 'web'
        $arrayOfWebPermissions = [
            'access promos',
            'access amenities',
            'control devices',
        ];

        $admin = collect($arrayOfAdminPermissions)->map(function ($permission) {
            return [
                'name'       => $permission,
                'guard_name' => 'admin',
            ];
        });

        $web = collect($arrayOfWebPermissions)->map(function ($permission) {
            return [
                'name'       => $permission,
                'guard_name' => 'web',
            ];
        });

        $permissions = $admin->merge($web);

        Permission::insert($permissions->toArray());
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> giveAdminPermissionTo
     * <Function> Assigning all admin permissions to a user
     *
     * @param \App\Models\User $user
     * @return void
     */
    protected function giveAdminPermissionsTo(User $user): void
    {
        $permissions = Permission::where('guard_name', 'admin')->pluck('name');
        $user->givePermissionTo($permissions);
    }
}

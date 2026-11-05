<?php

namespace Database\Seeders;

use App\Enums\PredefinedRolePermissions;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // Seed roles and permissions
        $this->seedRolesAndPermissions();
        // Assign roles and permissions to super admin
        $this->assignRolesAndPermissions();
    }
    /**
     * Create permissions if they don't exist.
     */
    private function seedRolesAndPermissions()
    {
        $methods = get_class_methods(PredefinedRolePermissions::class);
        foreach ($methods as $method) {
            $roleData = PredefinedRolePermissions::$method();
            // Create Role
            $role = Role::firstOrCreate([
                'name' => $roleData["name"],
                'guard_name' => $roleData["guard"]
            ]);

            // Crete Permissions
            foreach ($roleData["permissions"] as $permission) {
                $permission = Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => $roleData["guard"],
                ]);
            }
            $role->syncPermissions($roleData["permissions"]);
        }
    }
    /**
     * Assign roles to specific users/admins.
     */
    private function assignRolesAndPermissions()
    {
        // Assign 'Super Admin' role to a specific admin
        $superAdmin = Admin::where('username', 'like', '%super_admin%')->first();

        $superAdminRole = PredefinedRolePermissions::superAdmin();
        if ($superAdmin && !$superAdmin->hasRole($superAdminRole["name"])) {
            $superAdmin->assignRole($superAdminRole["name"]);
        }
    }
}

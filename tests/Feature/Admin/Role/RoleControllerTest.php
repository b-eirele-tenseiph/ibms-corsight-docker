<?php

namespace Tests\Feature\Admin\Role;

use App\Http\Controllers\Admin\Settings\RoleController;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\{Data, Roles};

class RoleControllerTest extends TestCase
{
    use Data, Roles, RefreshDatabase, WithFaker;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // setUp                                                              (1.0)
    // createSuperAdminUser                                               (2.0)
    // createAdminRoleUser                                                (3.0)
    // createStaffUser                                                    (4.0)
    // createRoleData                                                     (5.0)
    // test_super_admin_role_has_permissions_to_manage_general_settings   (6.0)
    // test_super_admin_that_has_permissions_can_manage_general_settings  (7.0)
    // test_it_creates_a_new_role_with_permissions                        (8.0)
    // test_it_updates_an_existing_role_and_its_permissions               (9.0)
    // test_it_deletes_roles                                              (10.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> setUp
     * <Function> Method that is called before each test method.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createPermissions();
        $this->createSARole();
        $this->createAdminRole();
        $this->createStaffRole();
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> createSuperAdminUser
     * <Function> Creation of admin account with super admin role
     *
     * @return \App\Models\Admin
     */
    private function createSuperAdminUser(): Model
    {
        $admin = $this->createAdminUser();
        $admin->update(['enabled' => true]);
        $admin->assignRole('super admin');

        return $admin;
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> createAdminRoleUser
     * <Function> Creation of admin account with admin role
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    private function createAdminRoleUser(): mixed
    {
        return $this->createAdminUser()->assignRole('admin');
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> createStaffUser
     * <Function> Creation of admin account with staff role
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    private function createStaffUser(): mixed
    {
        return $this->createAdminUser()->assignRole('staff');
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> createRoleData
     * <Function> Generate role data for form input
     *
     * @param mixed $data
     * @return array
     */
    private function createRoleData($data = []): array
    {
        return [
            "name" => $data['name'] ?? $this->faker->unique()->word(),
            "guard_name" => $data['guard_name'] ?? "admin",
            "permissions" => $data['permissions'] ?? [
                ["name" => "manage users"],
                ["name" => "manage rooms"],
                ["name" => "manage general settings"],
            ]
        ];
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_admin_role_has_permissions_to_manage_room_management
     * <Function> Check if the super admin account has manage master data permission
     *
     * @return void
     */
    public function test_super_admin_role_has_permissions_to_manage_general_settings(): void
    {
        $role = Role::where('name', 'super admin')->first();

        $this->assertNotNull($role);

        $hasPermission = $role->hasPermissionTo('manage general settings');

        $this->assertTrue($hasPermission);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_super_admin_role_has_permissions_to_manage_master_data
     * <Function> Check if the super admin account can manage master data page
     *
     * @return void
     */
    public function test_super_admin_that_has_permissions_can_manage_general_settings(): void
    {
        $this->actingAs($this->createSuperAdminUser(), 'admin')
            ->get(action([RoleController::class, 'index']))
            ->assertOk()
            ->assertInertia(fn(Assert $page) => $page->component('admin/role/Index'));
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> test_it_creates_a_new_role_with_permissions
     * <Function> Create a new role and attach permissions, then verify.
     *
     * @return void
     */
    public function test_super_admin_with_permission_can_create_new_role(): void
    {
        // Fetch the permissions as objects/arrays with their names
        $permissions = Permission::whereIn('name', [
            "manage users",
            "manage rooms",
            "manage general settings"
        ])->get()->map(function ($permission) {
            return ['name' => $permission->name];
        })->toArray();

        $data = [
            "name" => $this->faker->unique()->word(),
            "guard_name" => "admin",
            "permissions" => $permissions,
        ];

        $admin = $this->createSuperAdminUser();

        $this->actingAs($admin, 'admin')
            ->post(action([RoleController::class, 'store']), $data)
            ->assertRedirect();

        $this->assertDatabaseHas(Role::class, collect($data)->only(['name', 'guard_name'])->toArray());

        $this->assertDatabaseCount(Role::class, 4);

        $newRole = Role::where('name', $data['name'])->first();
        $this->assertTrue($newRole->hasPermissionTo('manage general settings'));
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> test_it_updates_an_existing_role_and_its_permissions
     * <Function> Update an existing role's name and permissions.
     *
     * @return void
     */
    public function test_it_updates_an_existing_role_and_its_permissions(): void
    {
        $role = Role::with('permissions')->where('name', 'admin')->first();

        // ✅ Create the permission and get its ID
        $permission = Permission::firstOrCreate([
            'name' => 'control devices',
            'guard_name' => 'web',
        ]);

        $newData = [
            'name' => 'guest-' . uniqid(),
            'guard_name' => 'web',
            'permissions' => [
                $permission->id,
            ],
        ];

        $this->actingAs($this->createSuperAdminUser(), 'admin')
            ->put(action([RoleController::class, 'update'], $role->id), $newData)
            ->assertRedirect();

        $role->refresh();
        $this->assertEquals($newData['name'], $role->name);
        $this->assertDatabaseHas(Role::class, collect($newData)->only(['name', 'guard_name'])->toArray());
    }

    /**
     *<Layer number> (10.0)
     *
     * <Processing name> test_it_deletes_roles
     * <Function> Delete one or more roles and confirm removal.
     *
     * @return void
     */
    public function test_super_admin_with_permission_can_delete_role(): void
    {
        $role = Role::with('permissions')->where('name', 'admin')->first();

        $this->actingAs($this->createSuperAdminUser(), 'admin')
            ->delete(action([RoleController::class, 'delete'], ['ids' => [$role->id]]))
            ->assertRedirect();
    }
}

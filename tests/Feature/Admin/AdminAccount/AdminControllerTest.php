<?php

namespace Tests\Feature\Admin\AdminAccount;

use App\Models\Admin;
use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\Data;
use Spatie\Permission\Models\Permission;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker, Data;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $role                                                               (1.0)
    // setUp                                                               (2.0)
    // createSuperAdminUser                                                (3.0)
    // test_admin_can_view_admin_accounts_with_pagination_and_filters      (4.0)
    // test_super_admin_can_store_admin_account                            (5.0)
    // test_super_admin_can_update_admin_account                           (6.0)
    // test_super_admin_can_delete_admin_account                           (7.0)
    // test_super_admin_can_toggle_admin_enabled_status                    (8.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $role
     * <Function> The role to be used in tests.
     *
     * @var \Spatie\Permission\Models\Role
     */
    protected $role;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> setUp
     * <Function> Method that is called before each test method.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->role = Role::firstOrCreate(
            ['name' => 'admin'],
            ['guard_name' => 'admin']
        );
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> createSuperAdminUser
     * <Function> Creation of admin account with super admin role.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    private function createSuperAdminUser(): Admin
    {
        $permission = Permission::firstOrCreate([
            'name' => 'manage admin accounts',
            'guard_name' => 'admin',
        ]);

        $admin = Admin::factory()->create(['enabled' => true]);
        $admin->givePermissionTo($permission);

        return $admin;
    }
    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_admin_can_view_admin_accounts_with_pagination_and_filters
     * <Function> Check if the super admin can view the admin account page with pagination and filters.
     *
     * @return void
     */
    public function test_admin_can_view_admin_accounts_with_pagination_and_filters(): void
    {
        // Authenticate as the super admin user
        $this->actingAs($this->createSuperAdminUser(), 'admin');

        // Create multiple admins for pagination and filtering
        $admins = Admin::factory()->count(5)->create();
        $admins->each(fn($admin) => $admin->assignRole($this->role));

        // Make the request with pagination and filters
        $response = $this->get(route('admin.management.accounts.admins.index', [
            'role' => $this->role->id,
            'page' => 1
        ]))->assertStatus(200);

        // Get the last created admin's ID for comparison
        $lastCreatedAdminId = $admins->sortByDesc('created_at')->values()->first()->id;

        // Assert the Inertia response structure
        $response->assertInertia(function (AssertableInertia $page) use ($lastCreatedAdminId) {
            $page->component('admin/admin-account/Index')
                ->has('admins.data', 5)
                ->where('admins.data.0.id', $lastCreatedAdminId)
                ->has('filters')
                ->has('roles');
        });
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_super_admin_can_store_admin_account
     * <Function> Check if the super admin can store an admin account.
     *
     * @return void
     */
    public function test_super_admin_can_store_admin_account(): void
    {
        // Create and authenticate as the super admin user
        $this->actingAs($this->createSuperAdminUser(), 'admin');  // Authenticate the super admin user

        // Prepare the new admin account data
        $newData = [
            'username' => 'john_doe',
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'password' => 'password123',
            'role' => 'admin',
        ];

        // Make the request to store the admin account and assert redirect
        $response = $this->post(route('admin.management.accounts.admins.store'), $newData)
            ->assertStatus(302)
            ->assertRedirect();


        // Assert that the flash message for successful addition is present in the session
        $response->assertSessionHas('message.body', __('flash.added', ['attrib' => 'Admin Account']));

        // Retrieve the first Admin from the database
        $admin = Admin::orderBy('id', 'desc')->first();

        // Assert that the admin account was stored correctly
        $this->assertEquals('John Doe', $admin->name, 'Admin name did not store correctly');
        $this->assertEquals('johndoe@example.com', $admin->email, 'Admin email did not store correctly');
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_super_admin_can_update_admin_account
     * <Function> Check if the super admin can update an admin account.
     *
     * @return void
     */
    public function test_super_admin_can_update_admin_account(): void
    {
        // Create a super admin user
        $user = $this->createSuperAdminUser();

        // Authenticate as the super admin user
        $this->actingAs($user, 'admin');

        // Create an Admin instance
        $admin = Admin::factory() // This ensures the 'admin' role is assigned automatically
            ->create([
                'username' => 'old_name',
                'name' => 'Old Name',
                'email' => 'oldemail@example.com',
            ]);

        $admin->assignRole('admin');
        // Prepare the updated admin account data
        $updatedData = [
            'username' => 'Updated_name',
            'name' => 'Updated Name',
            'email' => 'updatedemail@example.com',
            'role' => $admin->roles->first()->name,
        ];

        // Make the request to update the admin account and assert redirect
        $response = $this->put(route('admin.management.accounts.admins.update', $admin->id), $updatedData)
            ->assertStatus(302)
            ->assertRedirect();


        // Assert that the flash message for successful update is present in the session
        $response->assertSessionHas('message.body', __('flash.updated', ['attrib' => 'Admin Account']));

        // Retrieve the updated Admin from the database
        $admin = Admin::find($admin->id);

        // Assert that the admin data was updated correctly
        $this->assertEquals('Updated Name', $admin->name, 'Admin name did not update correctly');
        $this->assertEquals('updatedemail@example.com', $admin->email, 'Admin email did not update correctly');
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_super_admin_can_delete_admin_account
     * <Function> Check if the super admin can delete an admin account.
     *
     * @return void
     */
    public function test_super_admin_can_delete_admin_account(): void
    {
        // Create a super admin user
        $user = $this->createSuperAdminUser();

        // Authenticate as the super admin user
        $this->actingAs($user, 'admin');

        // Create an Admin instance
        $admin = Admin::factory()->create();

        // Make the request to delete the admin account
        $response = $this->delete(route('admin.management.accounts.admins.destroy', $admin->id));

        // Assert that the flash message for successful deletion is present in the session
        $response->assertSessionHas('message.body', __('flash.deleted', ['attrib' => 'Admin Account']));

        // Assert the admin account is removed from the database
        $this->assertDatabaseMissing('admins', ['id' => $admin->id]);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> test_super_admin_can_toggle_admin_enabled_status
     * <Function> Check if the super admin can toggle an admin's enabled status.
     *
     * @return void
     */
    public function test_super_admin_can_toggle_admin_enabled_status(): void
    {
        // Create a super admin user
        $user = $this->createSuperAdminUser();

        // Authenticate as the super admin user
        $this->actingAs($user, 'admin');

        // Create an Admin instance with 'enabled' set to true
        $admin = Admin::factory()->create(['enabled' => 0]);

        // Make the request to toggle the enabled status of the admin
        $response = $this->put(route('admin.management.accounts.admins.enable', $admin->id))
            ->assertStatus(302)
            ->assertRedirect();

        // Assert that the flash message for successful update is present in the session
        $response->assertSessionHas('message.body', __('flash.updated', ['attrib' => 'Admin Account']));

        // Use assertDatabaseHas to check if the 'enabled' field was toggled to false
        $this->assertDatabaseHas('admins', [
            'id' => $admin->id,
            'enabled' => 1,
        ]);
    }
}

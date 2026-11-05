<?php

namespace Tests\Feature\Admin\RoleImpersonation;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\Roles;

class RoleImpersonationControllerTest extends TestCase
{
    use RefreshDatabase, Roles;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // setUp                                                              (1.0)
    // createSuperAdminUser                                               (2.0)
    // test_role_returns_only_admin_guard_roles                           (3.0)
    // test_role_excludes_super_admin                                     (4.0)
    // test_store_sets_impersonating_role_in_session                      (5.0)
    // test_store_redirects_to_the_correct_role_dashboard                 (6.0)
    // test_store_fails_validation_when_role_is_invalid                   (7.0)
    // test_destroy_removes_impersonating_role_from_session               (8.0)
    // test_destroy_redirects_to_admin_dashboard                          (9.0)

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

        $this->createSARole();

        $admin = $this->createSuperAdminUser();

        $this->actingAs($admin, 'admin');
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> createSuperAdminUser
     * <Function> Creation of admin account with super admin role.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    private function createSuperAdminUser()
    {
        return Admin::factory()->create(['enabled' => true])->assignRole('super admin');
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> test_role_returns_only_admin_guard_roles
     * <Function> Test if the method returns roles with admin guard.
     *
     * @return void
     */
    public function test_role_returns_only_admin_guard_roles(): void
    {
        Role::create(['name' => 'crew',        'guard_name' => 'admin']);
        Role::create(['name' => 'staff',       'guard_name' => 'admin']);
        Role::create(['name' => 'engineering', 'guard_name' => 'web']);

        $response = $this->getJson(route('admin.management.impersonate.index'));

        $response->assertOk()
            ->assertJsonFragment(['crew'])
            ->assertJsonFragment(['staff'])
            ->assertJsonMissing(['engineering']);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_role_excludes_super_admin
     * <Function> Test if the method returns roles with admin guard except for super admin.
     *
     * @return void
     */
    public function test_role_excludes_super_admin(): void
    {
        Role::create(['name' => 'crew',        'guard_name' => 'admin']);

        $response = $this->getJson(route('admin.management.impersonate.index'));

        $response->assertOk()
            ->assertJsonFragment(['crew'])
            ->assertJsonMissing(['super admin']);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_store_sets_impersonating_role_in_session
     * <Function> Test if the method store the selected role in session.
     *
     * @return void
     */
    public function test_store_sets_impersonating_role_in_session(): void
    {
        Role::create(['name' => 'crew',       'guard_name' => 'admin']);

        $this->post(route('admin.management.impersonate.store'), [
            'role' => 'crew',
        ]);;

        $this->assertEquals('crew', session('impersonating_role'));
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_store_redirects_to_the_correct_role_dashboard
     * <Function> Verify whether changing the role redirects the user to their dashboard.
     *
     * @return void
     */
    public function test_store_redirects_to_the_correct_role_dashboard(): void
    {
        Role::create(['name' => 'crew',       'guard_name' => 'admin']);

        $response = $this->post(route('admin.management.impersonate.store'), [
            'role' => 'crew',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('crew.dashboard.index'));
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_store_fails_validation_when_role_is_invalid
     * <Function> Verify whether an error is triggered when an invalid role is selected.
     *
     * @return void
     */
    public function test_store_fails_validation_when_role_is_invalid(): void
    {
        $response = $this->post(route('admin.management.impersonate.store'), [
            'role' => 'supervisor',
        ]);

        $response->assertSessionHasErrors('role');
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> test_destroy_removes_impersonating_role_from_session
     * <Function> Check whether invoking the method clears the session.
     *
     * @return void
     */
    public function test_destroy_removes_impersonating_role_from_session(): void
    {
        session(['impersonating_role' => 'crew']);

        $this->post(route('admin.management.impersonate.destroy'));

        $this->assertNull(session('impersonating_role'));
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> test_destroy_redirects_to_admin_dashboard
     * <Function> Check whether invoking the method redirect to admin dashboard.
     *
     * @return void
     */
    public function test_destroy_redirects_to_admin_dashboard(): void
    {
        session(['impersonating_role' => 'staff']);

        $response = $this->post(route('admin.management.impersonate.destroy'));

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.dashboard.index'));
    }
}

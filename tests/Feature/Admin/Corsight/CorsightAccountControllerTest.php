<?php

namespace Tests\Feature\Admin\Corsight;

use App\Models\CorsightAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Traits\Data;

class CorsightAccountControllerTest extends TestCase
{
    use Data, RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // createCorsightAdmin                                                 (1.0) 
    // test_admin_can_view_corsight_account_with_sorting                   (2.0) 
    // test_admin_can_search_corsight_accounts                             (3.0) 
    // test_super_admin_can_store_corsight_account                         (4.0) 
    // test_super_admin_can_update_corsight_account                        (5.0) 
    // test_super_admin_can_delete_corsight_account                        (6.0) 
    // test_get_credentials_returns_decrypted_values                       (7.0) 
    // test_admin_without_permission_cannot_access                         (8.0) 

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> createCorsightAdmin
     * <Function> Creation of admin account with the manage corsight
     *            permission.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    private function createCorsightAdmin()
    {
        $permission = Permission::firstOrCreate([
            'name' => 'manage corsight',
            'guard_name' => 'admin',
        ]);
        $admin = $this->createAdminUser();
        $admin->givePermissionTo($permission);

        return $admin;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> test_admin_can_view_corsight_account_with_sorting
     * <Function> Check if the admin can view the corsight account page with
     *            sorting.
     *
     * @return void
     */
    public function test_admin_can_view_corsight_account_with_sorting(): void
    {
        $this->actingAs($this->createCorsightAdmin(), 'admin');

        CorsightAccount::factory()->create(['username' => 'alice-test']);
        CorsightAccount::factory()->create(['username' => 'charlie-test']);
        CorsightAccount::factory()->create(['username' => 'bob-test']);

        $response = $this->get(route('admin.management.corsight.accounts.index', [
            'field' => 'username',
            'direction' => 'desc',
        ]))->assertStatus(200);

        $response->assertInertia(function (AssertableInertia $page) {
            $page->component('admin/corsight-account/Index')
                ->has('accounts.data', 3)
                ->where('accounts.data.0.username', 'charlie-test')
                ->has('filters');
        });
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> test_admin_can_search_corsight_accounts
     * <Function> Check if the admin can filter accounts by username search.
     *
     * @return void
     */
    public function test_admin_can_search_corsight_accounts()
    {
        $this->actingAs($this->createCorsightAdmin(), 'admin');

        CorsightAccount::factory()->create(['username' => 'ibms-integration']);
        CorsightAccount::factory()->create(['username' => 'other-user']);

        $response = $this->get(route('admin.management.corsight.accounts.index', [
            'search' => 'ibms',
        ]))->assertStatus(200);

        $response->assertInertia(function (AssertableInertia $page) {
            $page->component('admin/corsight-account/Index')
                ->has('accounts.data', 1)
                ->where('accounts.data.0.username', 'ibms-integration');
        });
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_super_admin_can_store_corsight_account
     * <Function> Check if the admin can store corsight account data.
     *
     * @return void
     */
    public function test_super_admin_can_store_corsight_account()
    {
        $this->actingAs($this->createCorsightAdmin(), 'admin');

        $newData = [
            'username' => 'ibms-integration',
            'password' => 'test-password',
            'endpoint_secret' => 'test-webhook-secret',
            'endpoint_url' => 'https://example.local/api/corsight/endpoint',
        ];

        $response = $this->post(route('admin.management.corsight.accounts.store'), $newData)
            ->assertStatus(302)
            ->assertRedirect();

        $response->assertSessionHas('message.body', __('flash.added', ['attrib' => 'Account']));

        $this->assertDatabaseHas('corsight_account', [
            'username' => 'ibms-integration',
            'endpoint_url' => 'https://example.local/api/corsight/endpoint',
        ]);

        // Stored encrypted, readable decrypted
        $account = CorsightAccount::first();
        $this->assertEquals('test-password', $account->password);
        $this->assertEquals('test-webhook-secret', $account->endpoint_secret);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_super_admin_can_update_corsight_account
     * <Function> Check if the admin can update corsight account data.
     *
     * @return void
     */
    public function test_super_admin_can_update_corsight_account()
    {
        $this->actingAs($this->createCorsightAdmin(), 'admin');

        $account = CorsightAccount::factory()->create([
            'username' => 'old-user',
            'password' => 'old-password',
            'endpoint_secret' => 'old-secret',
        ]);

        $updatedData = [
            'username' => 'new-user',
            'password' => 'new-password',
            'endpoint_secret' => 'new-secret',
            'endpoint_url' => $account->endpoint_url,
        ];

        $response = $this->put(route('admin.management.corsight.accounts.update', $account->id), $updatedData)
            ->assertStatus(302)
            ->assertRedirect();

        $response->assertSessionHas('message.body', __('flash.updated', ['attrib' => 'Account']));

        $account->refresh();
        $this->assertEquals('new-user', $account->username);
        $this->assertEquals('new-password', $account->password);
        $this->assertEquals('new-secret', $account->endpoint_secret);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_super_admin_can_delete_corsight_account
     * <Function> Check if the admin can delete corsight account data.
     *
     * @return void
     */
    public function test_super_admin_can_delete_corsight_account()
    {
        $this->actingAs($this->createCorsightAdmin(), 'admin');

        $account = CorsightAccount::factory()->create();

        $response = $this->delete(route('admin.management.corsight.accounts.destroy', $account->id));

        $response->assertSessionHas('message.body', __('flash.deleted', ['attrib' => 'Account']))->assertRedirect();

        $this->assertDatabaseCount('corsight_account', 0);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_get_credentials_returns_decrypted_values
     * <Function> Check if the credentials endpoint returns decrypted values.
     *
     * @return void
     */
    public function test_get_credentials_returns_decrypted_values()
    {
        $this->actingAs($this->createCorsightAdmin(), 'admin');

        $account = CorsightAccount::factory()->create([
            'password' => 'plain-password',
            'endpoint_secret' => 'plain-endpoint-secret',
            'endpoint_url' => 'https://example.local/api/corsight/endpoint',
        ]);

        $this->post(route('admin.management.corsight.accounts.credentials', $account->id))
            ->assertStatus(200)
            ->assertJson([
                'username' => $account->username,
                'password' => 'plain-password',
                'endpoint_secret' => 'plain-endpoint-secret',
                'endpoint_url' => 'https://example.local/api/corsight/endpoint',
            ]);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> test_admin_without_permission_cannot_access
     * <Function> Check that an admin without the manage corsight permission
     *            is rejected.
     *
     * @return void
     */
    public function test_admin_without_permission_cannot_access()
    {
        $this->actingAs($this->createAdminUser(), 'admin');

        $this->get(route('admin.management.corsight.accounts.index'))->assertStatus(403);
    }
}

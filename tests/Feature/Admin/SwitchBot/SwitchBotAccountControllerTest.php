<?php

namespace Tests\Feature\Admin\SwitchBot;

use App\Models\SwitchBotAccount;
use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Hash, Http, Storage};
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Traits\Data;

class SwitchBotAccountControllerTest extends TestCase
{
    use Data, RefreshDatabase, WithFaker;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // setUp                                                              (1.0)
    // createSuperAdminUser                                               (2.0)
    // createRoomData                                                     (3.0)
    // test_admin_can_view_switchbot_account_with_pagination_and_sorting  (4.0)
    // test_super_admin_can_store_switchbot_account                       (5.0)
    // test_super_admin_can_update_switchbot_account                      (6.0)
    // test_super_admin_can_delete_switchbot_account                      (7.0)
    // test_get_authentication_credentials                                (8.0)
    // test_admin_can_download_template_switch_bot_account                (9.0)
    // test_admin_can_export_switch_bot_account                           (10.0)
    // test_admin_can_import_switch_bot_account                           (11.0)

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
        $permission = Permission::firstOrCreate([
            'name' => 'manage switchbot',
            'guard_name' => 'admin',
        ]);
        $admin = $this->createAdminUser();
        $admin->givePermissionTo($permission);

        return $admin;
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> createRoomData
     * <Function> Creation of room data.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function createRoomData()
    {
        return $this->createRoom();
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_admin_can_view_switchbot_account_with_pagination_and_sorting
     * <Function> Check if the super admin can view the switchbot account page.
     *
     * @return void
     */
    public function test_admin_can_view_switchbot_account_with_pagination_and_sorting(): void
    {
        // Authenticate as the super admin user
        $this->actingAs($this->createSuperAdminUser(), 'admin');

        // Create multiple accounts for pagination and sorting
        $accounts = SwitchBotAccount::factory()->count(5)->create(['room_id' => $this->createRoomData()->id]);
        $accounts = SwitchBotAccount::all();

        // Make the request with pagination and sorting parameters
        $response = $this->get(route('admin.management.switchbot.accounts.index', [
            'sortBy' => 'email',
            'sortOrder' => 'desc',
            'page' => 1
        ]))->assertStatus(200);

        // Get the last created account's ID for comparison
        $lastCreatedAccountId = $accounts->sortByDesc('created_at')->values()->first()->id;

        // Assert the Inertia response structure
        $response->assertInertia(function (AssertableInertia $page) use ($lastCreatedAccountId) {
            $page->component('admin/switch-bot-account/Index')
                ->has('accounts.data', 5)
                ->where('accounts.data.0.id', $lastCreatedAccountId)
                ->has('filters')
                ->has('rooms');
        });
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_super_admin_can_store_switchbot_account
     * <Function> Check if the super admin can store switchbot account data.
     *
     * @return void
     */
    public function test_super_admin_can_store_switchbot_account(): void
    {
        // Create a super admin user
        $user = $this->createSuperAdminUser();

        // Authenticate as the super admin user
        $this->actingAs($user, 'admin');

        // Prepare the new account data
        $newData = [
            'access_token'   => 'abc123def45678',
            'secret_key'     => 'xyz789ghi01234',
            'room_id'        => $this->createRoomData()->id,
            'email'          => 'test@gmail.com',
            'password'       => 'password123',
            'webhook_action' => 'setup',
            'webhook_url'    => 'http://ibms-office.local'
        ];

        // Fake HTTP responses for SwitchBot API endpoints
        Http::fake([
            'https://api.switch-bot.com/v1.1/devices' => Http::response([], 200),
            'v1.1/webhook/setupWebhook' => Http::response([], 200)
        ]);

        // Make the request to store the account and assert redirect
        $response = $this->post(route('admin.management.switchbot.accounts.store'), $newData)
            ->assertStatus(302)
            ->assertRedirect();

        // Assert that the flash message for successful addition is present in the session
        $response->assertSessionHas('message.body', __('flash.added', ['attrib' => 'Account']));

        // Retrieve the first SwitchBotAccount from the database
        $account = SwitchBotAccount::first();

        // Assert that the account data was stored correctly
        $this->assertEquals('abc123def45678', $account->access_token, 'Access token did not store correctly');
        $this->assertEquals('xyz789ghi01234', $account->secret_key, 'Secret key did not store correctly');
        $this->assertEquals('test@gmail.com', $account->email, 'Email did not store correctly');
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_super_admin_can_update_switchbot_account
     * <Function> Check if the super admin can update switchbot account data.
     *
     * @return void
     */
    public function test_super_admin_can_update_switchbot_account(): void
    {
        // Create a super admin user
        $user = $this->createSuperAdminUser();

        // Authenticate as the super admin user
        $this->actingAs($user, 'admin');

        // Create a SwitchBotAccount instance with associated room_id
        $account = SwitchBotAccount::factory()->create([
            'access_token' => encrypt('abc123def45678'),
            'secret_key' => encrypt('xyz789ghi01234'),
            'room_id' => $this->createRoomData()->id, // Set the room_id
        ]);

        // Prepare the updated data for the account
        $updatedData = [
            'access_token'   => 'abc123def456', // New token
            'secret_key'     => 'xyz789ghi012', // New secret
            'webhook_action' => 'update',
            'webhook_url'    => 'http://ibms-office.local'
        ];

        // Fake HTTP responses for SwitchBot API endpoints
        Http::fake([
            'https://api.switch-bot.com/v1.1/devices' => Http::response([], 200),
            'v1.1/webhook/updateWebhook' => Http::response([], 200)
        ]);

        // Make the request to update the account and assert redirect
        $response = $this->put(route('admin.management.switchbot.accounts.update', $account->id), $updatedData)
            ->assertStatus(302)
            ->assertRedirect();

        // Assert that the flash message for successful update is present in the session
        $response->assertSessionHas('message.body', __('flash.updated', ['attrib' => 'Account']));

        // Retrieve the updated SwitchBotAccount from the database
        $account = SwitchBotAccount::first();

        // Assert that the account data was updated correctly
        $this->assertEquals('abc123def456', $account->access_token, 'Access token did not update correctly');
        $this->assertEquals('xyz789ghi012', $account->secret_key, 'Secret key did not update correctly');
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_super_admin_can_delete_switchbot_account
     * <Function> Check if the super admin can delete switchbot account data.
     *
     * @return void
     */
    public function test_super_admin_can_delete_switchbot_account(): void
    {
        // Create a super admin user
        $user = $this->createSuperAdminUser();

        // Authenticate as the super admin user
        $this->actingAs($user, 'admin');

        // Create a SwitchBotAccount instance with associated room_id
        $account = SwitchBotAccount::factory()->create([
            'room_id' => $this->createRoomData()->id,
        ]);

        // Make the request to delete the account
        $response = $this->delete(route('admin.management.switchbot.accounts.destroy', $account->id));

        // Assert that the flash message for successful deletion is present in the session and assert redirect
        $response->assertSessionHas('message.body', __('flash.deleted', ['attrib' => 'Account']))->assertRedirect();
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> test_get_authentication_credentials
     * <Function> Check if the super admin can get authentication credentials of the switchbot account.
     *
     * @return /Illuminate/Testing/TestResponse
     */
    public function test_get_authentication_credentials()
    {
        // Create a super admin user
        $user = $this->createSuperAdminUser();

        // Authenticate as the super admin user
        $this->actingAs($user, 'admin');

        // Create a SwitchBotAccount instance with associated room_id and encrypted credentials
        $encryptedToken = encrypt('abc123def45678');
        $encryptedSecret = encrypt('xyz789ghi01234');
        $encryptedPassword = Hash::make('password');

        $account = SwitchBotAccount::factory()->create([
            'access_token' => $encryptedToken,
            'secret_key' => $encryptedSecret,
            'password' => $encryptedPassword,
            'room_id' => $this->createRoomData()->id,
        ]);

        // Make a POST request to retrieve authentication credentials and assert the response
        return $this->post(route('admin.management.switchbot.accounts.credentials', $account->id))
            ->assertStatus(200)
            ->assertJson([
                'token' => $encryptedToken,
                'secret' => $encryptedSecret,
                'password' => $encryptedPassword,
            ]);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> test_admin_can_download_template_switch_bot_account
     * <Function> Check if admin can download spreadsheet template
     *
     * @return void
     */
    public function test_admin_can_download_template_switch_bot_account()
    {
        // Create a super admin user
        $user = $this->createSuperAdminUser();

        // Authenticate as the super admin user
        $this->actingAs($user, 'admin');

        $this->get(route('admin.management.switchbot.accounts.generate-template'))
            ->assertOk()
            ->assertDownload('switchbot_account_template_' . now('Asia/Manila')->format('Ymd') . '.xlsx');
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> test_admin_can_export_switch_bot_account
     * <Function> Check if admin can export spreadsheet template
     *            that contains switchbot account
     *
     * @return void
     */
    public function test_admin_can_export_switch_bot_account()
    {
        // Create a super admin user
        $user = $this->createSuperAdminUser();

        // Authenticate as the super admin user
        $this->actingAs($user, 'admin');

        $this->get(route('admin.management.switchbot.accounts.export'))
            ->assertOk()
            ->assertDownload('switchbot_account_' . now('Asia/Manila')->format('Ymd') . '.xlsx');
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> test_admin_can_import_switch_bot_account
     * <Function> Check if admin can import spreadsheet template
     *
     * @return void
     */
    public function test_admin_can_import_switch_bot_account()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('switchbot_accounts.xlsx');

        // Create a super admin user
        $user = $this->createSuperAdminUser();

        // Authenticate as the super admin user
        $this->actingAs($user, 'admin')
            ->post('admin.management.switchbot.accounts.import', ['file' => $file]);

        $this->assertCount(0, SwitchBotAccount::all());
    }
}

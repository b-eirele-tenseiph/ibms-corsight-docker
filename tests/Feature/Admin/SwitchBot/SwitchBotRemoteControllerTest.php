<?php

namespace Tests\Feature\Admin\SwitchBot;

use App\Models\Room;
use App\Models\SwitchBotAccount;
use App\Models\SwitchBotHub;
use App\Models\SwitchBotRemote;
use App\Models\SwitchBotType;
use App\Services\SwitchBotRemote\SyncRemoteService;
use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use Maatwebsite\Excel\Facades\Excel;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\Traits\{Data, Roles};
use Tests\TestCase;

class SwitchBotRemoteControllerTest extends TestCase
{
    use Data, Roles, RefreshDatabase, WithFaker;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // setUp                                                              (1.0)
    // tearDown                                                           (2.0)
    // createSuperAdminUser                                               (3.0)
    // createAdminRoleUser                                                (4.0)
    // createStaffUser                                                    (5.0)
    // userPermissionToManageSwitchBotRemote                              (6.0)
    // test_admin_role_has_permissions_to_manage_switchbot_remote         (7.0)
    // _management
    // test_admin_can_manage_switchbot_remote_with_pagination_and_sorting (8.0)
    // test_super_admin_can_store_switchbot_remote                        (9.0)
    // test_super_admin_can_synchronize_switchbot_remote_successfully     (10.0)
    // test_admin_switchbot_remotes_export                                (11.0)
    // test_admin_can_catch_failed_accounts                               (12.0)

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
     * <Processing name> tearDown
     * <Function> Cleans up after each test method by closing Mockery to prevent test pollution.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> createSuperAdminUser
     * <Function> Creation of admin account with super admin role
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    private function createSuperAdminUser()
    {
        return $this->createAdminUser()->assignRole('super admin');
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> createAdminRoleUser
     * <Function> Creation of admin account with admin role
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    private function createAdminRoleUser()
    {
        return $this->createAdminUser()->assignRole('admin');
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> createStaffUser
     * <Function> Creation of admin account with staff role
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    private function createStaffUser()
    {
        return $this->createAdminUser()->assignRole('staff');
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> userPermissionToManageSwitchBotRemote
     * <Function> Check if the admin accounts can manage the SwitchBot Remote management page
     *
     * @return void
     */
    private function userPermissionToManageSwitchBotRemote(array $auth)
    {
        // Authenticate each given account as 'super admin' and verify access to the SwitchBot Remote Page
        foreach ($auth as $user) {
            $this->actingAs($user, 'admin')
                ->get(action([SwitchBotRemote::class, 'index']))
                ->assertOk()
                ->assertInertia(function (AssertableInertia $page) {
                    $page->component('admin/switch-bot-remote/Index');
                });
        }
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_admin_role_has_permissions_to_manage_switchbot_remote_management
     * <Function> Check if the admin role has permission to manage SwitchBot Remote
     *
     * @return void
     */
    public function test_admin_role_has_permissions_to_manage_switchbot_remote_management()
    {
        $roles = Role::whereIn('name', ['super admin', 'admin', 'staff'])->get();

        $this->assertNotNull($roles);

        $hasPermission = $roles->contains(function ($role) {
            return $role->hasPermissionTo('manage switchbot');
        });

        $this->assertTrue($hasPermission);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> test_admin_can_manage_switchbot_remote_with_pagination_and_sorting
     * <Function> Test if an admin can manage SwitchBot Remote with pagination and sorting
     *
     * @return void
     */
    public function test_admin_can_manage_switchbot_remote_with_pagination_and_sorting()
    {
        $user = $this->createSuperAdminUser();
        $this->actingAs($user, 'admin');

        $hub = SwitchBotHub::factory()->create();

        $remotes = SwitchBotRemote::factory()->count(5)->create(['hub_id' => $hub->id]);

        $response = $this->get(route('admin.management.switchbot.remotes.index', [
            'sort'      => 'created_at',
            'direction' => 'desc',
            'page'      => 1,
        ]));

        $response->assertStatus(200);

        $maxCreatedAt = $remotes->max('created_at');

        $expectedFirstId = $remotes
            ->filter(fn($r) => $r->created_at->eq($maxCreatedAt))
            ->sortByDesc('id')
            ->first()
            ->id;

        $response->assertInertia(function (AssertableInertia $page) use ($expectedFirstId) {
            $page->component('admin/switch-bot-remote/Index')
                ->has('remotes.data', 5)
                ->where('remotes.data', fn($items) => collect($items)->contains(fn($item) => $item['id'] === $expectedFirstId))
                ->has('filters')
                ->has('rooms');
        });
    }



    /**
     * <Layer number> (9.0)
     *
     * <Processing name> test_super_admin_can_store_switchbot_remote
     * <Function> Test if a super admin can store a new SwitchBot Remote
     *
     * @return void
     */
    public function test_super_admin_can_store_switchbot_remote()
    {
        // Create a Super Admin user
        $user = $this->createSuperAdminUser();

        // Authenticate as the super admin user
        $this->actingAs($user, 'admin');

        // Create a Hub instance
        $hub = SwitchBotHub::factory()->create();

        // Create or retrieve a type instance
        $type = SwitchBotType::factory()->create();

        // Ensure the 'serial_number' and 'name' are unique by appending uniqid or using faker's unique method
        $serialNumber = '01-202309050809-03258832-' . uniqid('', false);

        $newData = [
            'new' => [
                [
                    'name' => 'Test Remote ',
                    'serial_number' => $serialNumber,
                    'type_id' => $type->id,
                    'hub_id' => $hub->id
                ],
            ],
            'outdated' => [],
            'updated'  => [],
        ];

        // Make the request to store the remote
        $response = $this->post(route('admin.management.switchbot.remotes.store'), $newData);

        // Assert the response status
        $response->assertStatus(302);
        $response->assertRedirect(route('admin.management.switchbot.remotes.index'));

        // Assert the session message
        $expectedMessage = __('flash.synced', ['attrib' => 'Remotes']);
        $response->assertSessionHas('message.body', $expectedMessage);

        // Retrieve the remote and assert that the data is correct
        $remote = SwitchBotRemote::first();
        $this->assertEquals('Test Remote', $remote->name, 'Name did not store correctly');
        $this->assertEquals($serialNumber, $remote->serial_number, 'Serial number did not store correctly');
        $this->assertEquals($type->id, $remote->type_id, 'Type Id did not store correctly');
        $this->assertEquals($hub->id, $remote->hub_id, 'Hub Id did not store correctly');
    }



    /**
     * <Layer number> (10.0)
     *
     * <Processing name> test_super_admin_can_synchronize_switchbot_remote_successfully
     * <Function> Test if a super admin can synchronize SwitchBot Remote successfully
     *
     * @return void
     */
    public function test_super_admin_can_synchronize_switchbot_remote_successfully()
    {
        // Create a Super Admin user
        $user = $this->createSuperAdminUser();

        //  Authenticate as the Super Admin user
        $this->actingAs($user, 'admin');

        // Create a mock instance of SyncRemoteService
        $serviceMock = Mockery::mock(SyncRemoteService::class);
        $serviceMock->shouldReceive('handle')
            ->once()
            ->andReturn([
                'list' => collect(),
                'new' => collect(),
                'outdated' => collect(),
                'existing' => collect(),
                'conflict' => collect()
            ]);

        $this->app->instance(SyncRemoteService::class, $serviceMock);

        // Sending a Get Request to the corresponding route
        $response = $this->getJson(route('admin.management.switchbot.remotes.sync'));

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'list',
                'new',
                'outdated',
                'existing',
                'conflict'
            ]);
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> test_admin_switchbot_remotes_export
     * <Function> Test if the export function is working.
     *
     * @return void
     */
    public function test_admin_switchbot_remotes_export()
    {
        $admin = $this->createSuperAdminUser();

        $this->actingAs($admin, 'admin');

        Excel::fake();

        $this->get(route('admin.management.switchbot.remotes.export'))->assertOk();

        Excel::assertDownloaded('switchbot_remotes_' . now('Asia/Manila')->format('Ymd') . '.xlsx');
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> test_admin_can_catch_failed_accounts
     * <Function> Test if the function can catch failed accounts.
     *
     * @return void
     */
    public function test_admin_can_catch_failed_accounts(): void
    {
        $this->actingAs($this->createSuperAdminUser(), 'admin');

        $room = Room::factory()->create([
            'name' => 'Failed Room',
        ]);

        SwitchBotAccount::factory()->create([
            'room_id' => $room->id,
            'access_token' => 'invalid-token',
            'secret_key' => 'invalid-secret',
        ]);

        Http::fake([
            '*' => Http::response([
                'statusCode' => 190,
                'message' => 'Unauthorized',
                'body' => [],
            ], 401),
        ]);

        $response = $this->getJson(route('admin.management.switchbot.devices.sync'));

        $response->assertOk();

        $response->assertJsonPath('failed.0.room_id', $room->id);
        $response->assertJsonPath('failed.0.room_name', 'Failed Room');
        $response->assertJsonPath('failed.0.status', 401);

        $this->assertNotEmpty($response->json('failed.0.reason'));
    }
}

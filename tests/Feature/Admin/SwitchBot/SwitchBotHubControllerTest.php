<?php

namespace Tests\Feature\Admin\SwitchBot;

use App\Models\Admin;
use App\Models\Room;
use App\Models\RoomArea;
use App\Models\SwitchBotAccount;
use App\Models\SwitchBotHub;
use App\Models\SwitchBotType;
use App\Services\SwitchBotHub\SyncHubService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

class SwitchBotHubControllerTest extends TestCase
{
    use RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $adminAccount                                                       (1.0)
    // setUp                                                               (2.0)
    // createAndLoginAdmin                                                 (3.0)
    // test_index_displays_users_with_pagination                           (4.0)
    // test_admin_can_scan_hubs                                            (5.0)
    // test_admin_can_store_new_update_and_delete_hubs                     (6.0)
    // test_admin_can_update_a_switchbot_hub                               (7.0)
    // test_admin_can_export_switch_bot_hubs                               (8.0)
    // test_admin_can_catch_failed_accounts                                (9.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $adminAccount
     * <Function> Holds the currently authenticated admin account for testing.
     *
     * @var \App\Models\Admin
     */
    protected $adminAccount;


    /**
     * <Layer number> (2.0)
     *
     * <Processing name> setUp
     * <Function> Prepare the test environment and authenticate as admin before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createAndLoginAdmin();
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> createAndLoginAdmin
     * <Function> Create and authenticate an admin account for testing.
     *
     * @return void
     */
    protected function createAndLoginAdmin(): void
    {
        $this->adminAccount = Admin::factory()->create(['enabled' => true]);

        // Give the admin the 'manage switchbot' permission
        Permission::findOrCreate('manage switchbot', 'admin');
        $this->adminAccount->givePermissionTo('manage switchbot');

        $this->actingAs($this->adminAccount, 'admin');
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_index_displays_switch_bot_hubs_with_pagination
     * <Function> Test that the user management page displays paginated user records.
     *
     * @return void
     */
    public function test_index_displays_users_with_pagination()
    {
        $response = $this->get(route('admin.management.switchbot.hubs.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('admin/switch-bot-hub/Index')
                ->has('hubs.data')
        );
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_admin_can_scan_hubs
     * <Function> Test if the scan function is working.
     *
     * @return void
     */
    public function test_admin_can_scan_hubs()
    {
        $this->mock(
            SyncHubService::class,
            function ($mock) {
                $mock->shouldReceive('handle')->once()->andReturn([
                    'list' => [],
                    'new' => [],
                    'outdated' => [],
                    'updated' => [],
                ]);
            }
        );

        $response = $this->getJson(route('admin.management.switchbot.hubs.sync'));

        // If there's an unexpected status, dump it for debugging in CI logs
        if ($response->status() !== 200) {
            $response->dump(); // shows in GitHub Actions logs
        }

        $response->assertStatus(200);
        $response->assertExactJson([
            'list' => [],
            'new' => [],
            'outdated' => [],
            'updated' => [],
        ]);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_admin_can_store_new_update_and_delete_hubs
     * <Function> Test if the scan function is working.
     *
     * @return void
     */
    public function test_admin_can_store_new_update_and_delete_hubs()
    {
        $room = Room::factory()->create();
        $type = SwitchBotType::factory()->create();
        $roomArea = RoomArea::factory()->create();

        $hubToUpdate = SwitchBotHub::factory()->create([
            'name' => 'Old Hub',
            'serial_number' => 'sn-hub-update',
            'type_id' => $type->id,
            'room_id' => $room->id,
            'room_area_id' => $roomArea->id,
        ]);

        $hubToDelete = SwitchBotHub::factory()->create([
            'serial_number' => 'sn-hub-delete',
            'type_id' => $type->id,
            'room_id' => $room->id,
            'room_area_id' => $roomArea->id,
        ]);

        $newHub = [
            'name' => 'New Hub Name',
            'serial_number' => 'sn-hub-new',
            'type_id' => $type->id,
            'room_id' => $room->id,
            'room_area_id' => $roomArea->id,
        ];

        $updatedHub = [
            'name' => 'Update Hub Name',
            'serial_number' => 'sn-hub-update',
            'type_id' => $type->id,
            'room_id' => $room->id,
            'room_area_id' => $roomArea->id,
        ];

        $deletedHub = [
            'id' => $hubToDelete->id,
        ];

        $response = $this->postJson(route('admin.management.switchbot.hubs.sync-store'), [
            'new' => [$newHub],
            'outdated' => [$deletedHub],
            'updated' => [$updatedHub],
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.management.switchbot.hubs.index'));

        $this->assertDatabaseHas('switch_bot_hubs', [
            'serial_number' => 'sn-hub-new',
        ]);

        $this->assertDatabaseHas('switch_bot_hubs', [
            'serial_number' => 'sn-hub-update',
        ]);

        $this->assertDatabaseMissing('switch_bot_hubs', [
            'id' => $hubToDelete->id,
        ]);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_admin_can_update_a_switchbot_hub
     * <Function> Test if the scan function is working.
     *
     * @return void
     */
    public function test_admin_can_update_a_switchbot_hub()
    {
        $room = Room::factory()->create();
        $type = SwitchBotType::factory()->create();
        $roomAreaOld = RoomArea::factory()->create(['room_id' => $room->id]);
        $roomAreaNew = RoomArea::factory()->create(['room_id' => $room->id]);

        $hub = SwitchBotHub::factory()->create([
            'room_id' => $room->id,
            'type_id' => $type->id,
            'room_area_id' => $roomAreaOld->id,
            'name' => 'Old Hub Name',
        ]);

        $payload = [
            'room_name' => $room->name,
            'room_area_id' => $roomAreaNew->id,
            'name' => 'Updated Hub Name'
        ];

        $response = $this->from(route('admin.management.switchbot.hubs.index'))
            ->putJson(
                route('admin.management.switchbot.hubs.update', $hub->id),
                $payload
            );

        $response->assertStatus(302);
        $response->assertRedirect(route('admin.management.switchbot.hubs.index'));

        $this->assertDatabaseHas('switch_bot_hubs', [
            'room_id' => $room->id,
            'room_area_id' => $roomAreaNew->id,
            'name' => 'Updated Hub Name'
        ]);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> test_admin_can_export_switch_bot_hubs
     * <Function> Check if admin can export spreadsheet template
     *            that contains switchbot hubs
     *
     * @return void
     */
    public function test_admin_can_export_switch_bot_hubs()
    {
        $response = $this->get(route('admin.management.switchbot.hubs.export'));

        $response->assertOk()
            ->assertDownload('switchbot_hubs_' . now('Asia/Manila')->format('Ymd') . '.xlsx');
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> test_admin_can_catch_failed_accounts
     * <Function> Test if the function can catch failed accounts.
     *
     * @return void
     */
    public function test_admin_can_catch_failed_accounts(): void
    {
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

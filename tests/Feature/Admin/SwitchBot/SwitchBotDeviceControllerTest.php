<?php

namespace Tests\Feature\Admin\SwitchBot;

use App\Models\Admin;
use App\Models\Room;
use App\Models\SwitchBotAccount;
use App\Models\SwitchBotDevice;
use App\Models\SwitchBotType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Http;
use Tests\Traits\Data;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class SwitchBotDeviceControllerTest extends TestCase
{
    use Data, RefreshDatabase, WithFaker;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // createSuperAdminUser                                                (1.0)
    // test_admin_can_view_paginated_switchbot_devices_with_search         (2.0)
    // _sorting_and_filtering
    // test_admin_can_scan_devices                                         (3.0)
    // test_admin_can_store_update_and_delete_devices                      (4.0)
    // test_admin_can_update_a_switchbot_device                            (5.0)
    // test_admin_can_export_switch_bot_devices                            (6.0)
    // test_admin_can_catch_failed_accounts                                (7.0)

    protected function setUp(): void
    {
        parent::setUp();

        // Spatie caches roles/permissions across requests; clear between tests
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
    /**
     * <Layer number> (1.0)
     *
     * <Processing name> createSuperAdminUser
     * <Function> Creation of admin account with super admin role.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    private function createSuperAdminUser(): Admin
    {
        $permission = Permission::firstOrCreate([
            'name' => 'manage switchbot',
            'guard_name' => 'admin',
        ]);

        $admin = Admin::factory()->create(['enabled' => true]);
        $admin->givePermissionTo($permission);

        return $admin;
    }
    /**
     * <Layer number> (2.0)
     *
     * <Processing name> test_admin_can_view_paginated_switchbot_devices_with_search_sorting_and_filtering
     * <Function> Ensure the admin switchbot management page can be rendered successfully.
     *
     * @return void
     */
    public function test_admin_can_view_paginated_switchbot_devices_with_search_sorting_and_filtering(): void
    {
        $this->actingAs($this->createSuperAdminUser(), 'admin');

        $room = Room::factory()->create();
        $type = SwitchBotType::factory()->create();

        $device1 = SwitchBotDevice::factory()->create([
            'room_id' => $room->id,
            'type_id' => $type->id,
            'name' => 'Device A',
            'serial_number' => 'SN001'
        ]);
        $device1 = SwitchBotDevice::factory()->create([
            'room_id' => $room->id,
            'type_id' => $type->id,
            'name' => 'Device B',
            'serial_number' => 'SN002'
        ]);

        $response = $this->get('admin/management/switchbot/devices?search=Device A');

        $response->assertInertia(
            fn($page) =>
            $page->component('admin/switch-bot-device/Index')
                ->where('filters.search', 'Device A')
                ->has('devices.data', 1)
                ->where('devices.data.0.name', 'Device A')
        );

        $response = $this->get('admin/management/switchbot/devices?sort=name&direction=desc');
        $response->assertStatus(200);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> test_admin_can_scan_devices
     * <Function> Test if the scan function is working.
     *
     * @return void
     */
    public function test_admin_can_scan_devices(): void
    {
        $this->actingAs($this->createSuperAdminUser(), 'admin');

        $this->mock(\App\Services\SwitchBotDevice\SyncDeviceService::class, function ($mock) {
            $mock->shouldReceive('handle')->once()->andReturn([
                'list' => [],
                'new' => [],
                'outdated' => [],
                'updated' => [],
            ]);
        });

        $response = $this->getJson(route('admin.management.switchbot.devices.sync'));

        $response->assertJson([
            'list' => [],
            'new' => [],
            'outdated' => [],
            'updated' => [],
        ]);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_admin_can_store_update_and_delete_devices
     * <Function> Test if the store , update and delete of scan function is working.
     *
     * @return void
     */
    public function test_admin_can_store_update_and_delete_devices(): void
    {
        $this->actingAs($this->createSuperAdminUser(), 'admin');

        $room = Room::factory()->create();
        $type = SwitchBotType::factory()->create();

        $deviceToUpdate = SwitchBotDevice::factory()->create([
            'serial_number' => 'sn-update',
            'name' => 'Old Device',
            'room_id' => $room->id,
            'type_id' => $type->id,
        ]);
        $deviceToDelete = SwitchBotDevice::factory()->create([
            'serial_number' => 'sn-delete',
            'room_id' => $room->id,
            'type_id' => $type->id,
        ]);

        $newDevice = [
            'serial_number' => 'sn-new',
            'name' => 'New Device',
            'room_id' => $room->id,
            'type_id' => $type->id,
        ];

        $updatedDevice = [
            'serial_number' => 'sn-update',
            'name' => 'Updated Device Name',
            'room_id' => $room->id,
            'type_id' => $type->id,
        ];

        $deleteDevice = [
            'id' => $deviceToDelete->id,
        ];

        $response = $this->postJson(route('admin.management.switchbot.devices.sync-store'), [
            'new' => [$newDevice],
            'outdated' => [$deleteDevice],
            'updated' => [$updatedDevice],
        ]);

        $response->assertRedirect(route('admin.management.switchbot.devices.index'));

        $this->assertDatabaseHas('switch_bot_devices', [
            'serial_number' => 'sn-new',
            'name' => 'New Device',
        ]);

        $this->assertDatabaseHas('switch_bot_devices', [
            'serial_number' => 'sn-update',
            'name' => 'Updated Device Name',
        ]);

        $this->assertDatabaseMissing('switch_bot_devices', [
            'id' => $deviceToDelete->id,
        ]);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_admin_can_update_a_switchbot_device
     * <Function> Test if the Update function is working.
     *
     * @return void
     */
    public function test_admin_can_update_a_switchbot_device(): void
    {
        $this->actingAs($this->createSuperAdminUser(), 'admin');

        $room = Room::factory()->create();
        $type = SwitchBotType::factory()->create();
        $roomAreaOld = \App\Models\RoomArea::factory()->create(['room_id' => $room->id]);
        $roomAreaNew = \App\Models\RoomArea::factory()->create(['room_id' => $room->id]);

        $device = SwitchBotDevice::factory()->create([
            'room_id' => $room->id,
            'type_id' => $type->id,
            'room_area_id' => $roomAreaOld->id,
            'name' => 'Old Device Name',
        ]);

        $payload = [
            'room_area_id' => $roomAreaNew->id,
            'name' => 'Updated Device Name',
        ];

        $response = $this->from(route('admin.management.switchbot.devices.index'))
            ->putJson(
                route('admin.management.switchbot.devices.update', $device->id),
                $payload
            );

        $response->assertRedirect(route('admin.management.switchbot.devices.index'));
        $this->assertDatabaseHas('switch_bot_devices', [
            'id' => $device->id,
            'room_area_id' => $roomAreaNew->id,
            'name' => 'Updated Device Name',
        ]);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_admin_can_export_switch_bot_devices
     * <Function> Check if admin can export spreadsheet template
     *            that contains switchbot devices
     *
     * @return void
     */
    public function test_admin_can_export_switch_bot_devices()
    {
        // Create a super admin user
        $user = $this->createSuperAdminUser();

        // Authenticate as the super admin user
        $this->actingAs($user, 'admin');

        $this->get(route('admin.management.switchbot.devices.export'))
            ->assertOk()
            ->assertDownload('switchbot_devices_' . now('Asia/Manila')->format('Ymd') . '.xlsx');
    }

    /**
     * <Layer number> (7.0)
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

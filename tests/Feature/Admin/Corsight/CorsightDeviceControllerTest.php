<?php

namespace Tests\Feature\Admin\Corsight;

use App\Models\CorsightDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Traits\Data;

class CorsightDeviceControllerTest extends TestCase
{
    use Data, RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // createCorsightAdmin                                                 (1.0) 
    // test_admin_can_view_corsight_device                                 (2.0) 
    // test_super_admin_can_store_corsight_device                          (3.0) 
    // test_update_name_must_be_unique                                     (4.0) 
    // test_enable_data_storage_casts_to_boolean                           (5.0) 
    // test_super_admin_can_delete_corsight_device                         (6.0) 
    // test_admin_without_permission_cannot_access                         (7.0) 

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
     * <Processing name> test_admin_can_view_corsight_device
     * <Function> Check if the admin can view the corsight device page.
     *
     * @return void
     */
    public function test_admin_can_view_corsight_device(): void
    {
        $this->actingAs($this->createCorsightAdmin(), 'admin');

        CorsightDevice::factory()->count(5)->create();

        $response = $this->get(route('admin.management.corsight.devices.index'))->assertStatus(200);

        $response->assertInertia(function (AssertableInertia $page) {
            $page->component('admin/corsight-device/Index')
                ->has('devices.data', 5)
                ->has('filters')
                ->has('rooms');
        });
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> test_super_admin_can_store_corsight_device
     * <Function> Check if the admin can store corsight device data.
     *
     * @return void
     */
    public function test_super_admin_can_store_corsight_device()
    {
        $this->actingAs($this->createCorsightAdmin(), 'admin');

        $room = $this->createRoom();

        $newData = [
            'name' => 'Lobby Camera',
            'role' => 'general',
            'room_id' => $room->id,
            'room_area_id' => null,
        ];

        $response = $this->post(route('admin.management.corsight.devices.store'), $newData)
            ->assertStatus(302)
            ->assertRedirect();

        $response->assertSessionHas('message.body', __('flash.added', ['attrib' => 'Device']));

        $this->assertDatabaseHas('corsight_device', [
            'name' => 'Lobby Camera',
            'role' => 'general',
            'room_id' => $room->id,
            'enable_data_storage' => 1,
        ]);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_update_name_must_be_unique
     * <Function> Check that the device name stays unique on update.
     *
     * @return void
     */
    public function test_update_name_must_be_unique()
    {
        $this->actingAs($this->createCorsightAdmin(), 'admin');

        $device = CorsightDevice::factory()->create(['name' => 'Old Name']);
        CorsightDevice::factory()->create(['name' => 'Taken Name']);

        $this->put(route('admin.management.corsight.devices.update', $device->id), [
            'name' => 'Taken Name',
            'role' => 'general',
            'room_id' => $device->room_id,
            'room_area_id' => null,
        ])->assertSessionHasErrors('name');

        $this->put(route('admin.management.corsight.devices.update', $device->id), [
            'name' => 'New Name',
            'role' => 'general',
            'room_id' => $device->room_id,
            'room_area_id' => null,
        ])->assertSessionHas('message.body', __('flash.updated', ['attrib' => 'Device']));

        $device->refresh();
        $this->assertEquals('New Name', $device->name);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_enable_data_storage_casts_to_boolean
     * <Function> Check that the data storage flag round-trips as a boolean.
     *
     * @return void
     */
    public function test_enable_data_storage_casts_to_boolean()
    {
        $this->actingAs($this->createCorsightAdmin(), 'admin');

        CorsightDevice::factory()->create(['enable_data_storage' => 0]);

        $device = CorsightDevice::first();
        $this->assertFalse($device->enable_data_storage);

        $device->update(['enable_data_storage' => 1]);
        $device->refresh();
        $this->assertTrue($device->enable_data_storage);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_super_admin_can_delete_corsight_device
     * <Function> Check if the admin can delete corsight device data.
     *
     * @return void
     */
    public function test_super_admin_can_delete_corsight_device()
    {
        $this->actingAs($this->createCorsightAdmin(), 'admin');

        $device = CorsightDevice::factory()->create();

        $response = $this->delete(route('admin.management.corsight.devices.destroy', $device->id));

        $response->assertSessionHas('message.body', __('flash.deleted', ['attrib' => 'Device']))->assertRedirect();

        $this->assertDatabaseCount('corsight_device', 0);
    }

    /**
     * <Layer number> (7.0)
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

        $this->get(route('admin.management.corsight.devices.index'))->assertStatus(403);
    }
}

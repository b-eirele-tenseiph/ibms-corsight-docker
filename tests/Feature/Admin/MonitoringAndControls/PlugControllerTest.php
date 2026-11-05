<?php

namespace Tests\Feature\Admin\MonitoringAndControls;

use App\Models\Admin;
use App\Models\SwitchBotDevice;
use App\Models\Room;
use App\Models\RoomArea;
use App\Models\SwitchBotType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;

class PlugControllerTest extends TestCase
{
    use RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // createAdminWithmonitoringControlPermission                          (1.0)
    // test_loads_the_plugs_index_page                                     (2.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> createAdminWithmonitoringControlPermission
     * <Function>  Create an admin user and assign a specific permission.
     *
     * @param string $permissionName
     * @return \App\Models\Admin
     */
    private function createAdminWithmonitoringControlPermission(): Admin
    {
        $admin = Admin::factory()->create(['enabled' => true,]);

        $permission = Permission::firstOrCreate([
            'name' => 'monitoring controls',
            'guard_name' => 'admin',
        ]);

        $admin->givePermissionTo($permission);

        return $admin;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> test_loads_the_plugs_index_page
     * <Function> Load the plugs index page and verify its content.
     *
     * @return void
     */
    public function test_loads_the_plugs_index_page(): void
    {
        $admin = $this->createAdminWithmonitoringControlPermission();
        $this->actingAs($admin, 'admin');

        // Arrange: create data
        $room = Room::factory()->create(['name' => 'Test Room']);
        $area = RoomArea::factory()->create(['name' => 'Test Area']);
        $plugType = SwitchBotType::factory()->state(['name' => 'Plug'])->create();

        // Create a device (assumes `plugs()` scope includes this device)
        $device = SwitchBotDevice::factory()->create([
            'name' => 'Plug Device',
            'type_id' => $plugType->id,
            'room_id' => $room->id,
            'room_area_id' => $area->id,
        ]);

        // Act: visit the page
        $response = $this->get(route('admin.monitoring-controls.plugs.index', [
            'search' => 'Plug',
            'sort' => 'name',
            'direction' => 'asc',
        ]));

        // Assert: status and inertia component
        $response->assertStatus(200);

        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('admin/monitoring-plug/Index')
                ->has('appliances.data', 1)
                ->where('appliances.data.0.name', 'Plug Device')
                ->where('queryParams.search', 'Plug')
                ->where('queryParams.sort', 'name')
                ->where('queryParams.direction', 'asc')
        );
    }
}

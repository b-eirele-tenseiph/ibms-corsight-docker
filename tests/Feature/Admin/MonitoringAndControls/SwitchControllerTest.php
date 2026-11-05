<?php

namespace Tests\Feature\Admin\MonitoringAndControls;

use App\Models\Admin;
use App\Models\LorawanDevice;
use App\Models\Room;
use App\Models\RoomArea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;

class SwitchControllerTest extends TestCase
{
    use RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // setUp                                                               (1.0)
    // test_loads_the_switches_index_page                                  (2.0)

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
        // Spatie caches roles/permissions; clear between tests
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> test_loads_the_switches_index_page
     * <Function> Load the switches index page and verify its content.
     *
     * @return void
     */
    public function test_loads_the_switches_index_page(): void
    {
        $admin = Admin::factory()->create(['enabled' => true,]);
        $admin = Admin::factory()->create(['enabled' => true]);

        $permission = Permission::firstOrCreate([
            'name'       => 'monitoring controls',
            'guard_name' => 'admin',
        ]);
        // Ensure Admin model has protected $guard_name = 'admin'
        $admin->givePermissionTo($permission);

        $this->actingAs($admin, 'admin');
        // Arrange: create data
        $room = Room::factory()->create(['name' => 'Test Room']);
        $area = RoomArea::factory()->create(['name' => 'Test Area']);

        // Create a device (assumes `switches()` scope includes this device)
        $device = LorawanDevice::factory()->create([
            'name' => 'Switch Device',
            'model' => 'WS501',
            'room_id' => $room->id,
            'room_area_id' => $area->id,
        ]);

        // Act: visit the page
        $response = $this->get(route('admin.monitoring-controls.switches.index', [
            'search' => 'Switch',
            'sort' => 'name',
            'direction' => 'asc',
        ]));

        // Assert: status and inertia component
        $response->assertStatus(200);

        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('admin/monitoring-switch/Index')
                ->has('switches.data', 1)
                ->where('switches.data.0.name', 'Switch Device')
                ->where('queryParams.search', 'Switch')
                ->where('queryParams.sort', 'name')
                ->where('queryParams.direction', 'asc')
        );
    }
}

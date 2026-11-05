<?php

namespace Tests\Feature\Admin\MonitoringAndControls;

use App\Models\Admin;
use App\Models\LorawanDevice;
use App\Models\Room;
use App\Models\RoomArea;
use App\Models\SwitchBotRemote;
use App\Models\SwitchBotHub;
use App\Models\SwitchBotType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AirconControllerTest extends TestCase
{
    use RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // test_loads_the_aircon_index_page                                    (1.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> test_loads_the_aircon_index_page
     * <Function> This feature test covers the AirconController, ensuring that
     * the Aircon index page loads correctly with the expected data.
     *
     * @return void
     */
    public function test_loads_the_aircon_index_page(): void
    {
        $admin = Admin::factory()->create(['enabled' => true]);

        $permission = Permission::firstOrCreate([
            'name'       => 'monitoring controls',
            'guard_name' => 'admin',
        ]);
        // Ensure Admin model has protected $guard_name = 'admin'
        $admin->givePermissionTo($permission);

        $this->actingAs($admin, 'admin');

        // Arrange data
        $room = Room::factory()->create(['name' => 'Aircon Room']);
        $roomArea = RoomArea::factory()->create(['name' => 'Aircon Area']);

        $thermostat = LorawanDevice::factory()->create([
            'name' => 'UC300 Thermostat',
            'model' => 'UC300',
            'room_id' => $room->id,
            'room_area_id' => $roomArea->id,
        ]);

        $type = SwitchBotType::factory()->create([
            'category' => SwitchBotType::CATEGORY_REMOTE,
            'name' => 'Air Conditioner',
        ]);

        $hub = SwitchBotHub::factory()->create();

        $sbAircon = SwitchBotRemote::factory()->create([
            'name' => 'SB Aircon',
            'type_id' => $type->id,
            'hub_id' => $hub->id,
        ]);

        $response = $this->get(route('admin.monitoring-controls.aircons.index', [
            'search' => 'Aircon',
            'sortBy' => 'name',
            'sortOrder' => 'asc',
        ]));

        $response->assertOk();

        $response->assertInertia(
            fn(AssertableInertia $page) =>
            $page->component('admin/monitoring-climate/Index')
                ->has('aircons.data')
                ->where('queryParams.search', 'Aircon')
                ->where('queryParams.sortBy', 'name')
                ->where('queryParams.sortOrder', 'asc')
        );
    }
}

<?php

namespace Tests\Feature\Admin\Dashboard;

use App\Models\Admin;
use App\Models\Room;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Inertia\Testing\AssertableInertia as Assert;
use Mockery\MockInterface;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // test_admin_login_screen_can_be_rendered                             (1.0)
    // test_authenticated_admin_can_access_dashboard                       (2.0)
    // test_unauthenticated_admin_cannot_access_dashboard                  (3.0)
    // test_get_room_renders_devices_view_with_default_props               (4.0)
    // test_get_room_handles_request_filters_and_sorts_correctly           (5.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> test_admin_login_screen_can_be_rendered
     * <Function> Ensure the admin login page can be rendered successfully.
     *
     * @return void
     */
    public function test_admin_login_screen_can_be_rendered()
    {
        $response = $this->get('/admin/login');

        $response->assertStatus(200);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> test_authenticated_admin_can_access_dashboard
     * <Function> Verify that an enabled and authenticated admin can access the dashboard view.
     *
     * @return void
     */
    public function test_authenticated_admin_can_access_dashboard()
    {
        $admin = Admin::factory()->create([
            'enabled' => true,
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/dashboard');

        $response->assertOk();
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> test_unauthenticated_admin_cannot_access_dashboard
     * <Function> Ensure that unauthenticated admin users are redirected to the login page.
     *
     * @return void
     */
    public function test_unauthenticated_admin_cannot_access_dashboard()
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect(route('admin.login'));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_get_room_renders_devices_view_with_default_props
     * <Function> Ensure getRoom renders Devices with merged props and default payload from the admin service.
     *
     * @return void
     */
    public function test_get_room_renders_devices_view_with_default_props(): void
    {
        $admin = Admin::factory()->create(['enabled' => true]);
        $room  = Room::factory()->create();

        // Payload returned by AdminDashboardService::loadRoomDevicesAndFilters
        $payload = [
            'activeTab' => 'aircon',
            'queryParams' => [],
            'airconList' => [['id' => 2, 'name' => 'SB AC 1']],
        ];

        // Bind a mock AdminDashboardService so the controller gets our payload
        $this->mock(AdminDashboardService::class, function (MockInterface $mock) use ($payload) {
            $mock->shouldReceive('loadRoomDevicesAndFilters')
                ->once()
                ->andReturn($payload);
        });

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard.devices', ['room' => $room->id]));

        $response->assertOk();

        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('admin/dashboard-device/Index')
                ->where('room.id', $room->id)
                ->where('activeTab', 'aircon')
                ->where('queryParams', [])
        );
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_get_room_handles_request_filters_and_sorts_correctly
     * <Function> Ensure getRoom correctly handles specific filters and sorting parameters by echoing them in queryParams.
     *
     * @return void
     */
    public function test_get_room_handles_request_filters_and_sorts_correctly(): void
    {
        $admin = Admin::factory()->create(['enabled' => true]);
        $room  = Room::factory()->create();

        $query = [
            'sort' => 'name',
            'direction' => 'desc',
            'filters' => ['search' => 'desk'],
        ];

        $payload = [
            'activeTab' => 'aircon',
            'queryParams' => $query,
            'airconList' => [['id' => 2, 'name' => 'SB AC 1']],
        ];

        $this->mock(AdminDashboardService::class, function (MockInterface $mock) use ($payload) {
            $mock->shouldReceive('loadRoomDevicesAndFilters')
                ->once()
                ->andReturn($payload);
        });

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.dashboard.devices', array_merge(['room' => $room->id], $query)));

        $response->assertOk();

        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('admin/dashboard-device/Index')
                ->where('room.id', $room->id)
                ->where('queryParams.sort', 'name')
                ->where('queryParams.direction', 'desc')
                ->where('queryParams.filters.search', 'desk')
        );
    }
}

<?php

namespace Tests\Feature\Admin\Logs;

use App\Models\Admin;
use App\Services\ActivityLog\ActivityLogsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use Maatwebsite\Excel\Facades\Excel;

class ActivityLogsControllerTest extends TestCase
{
    use RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // createAdminWithManageRoomsPermission                                (1.0)
    // test_admin_logs_management_index                                    (2.0)
    // test_admin_logs_search_function                                     (3.0)
    // test_admin_logs_export                                              (4.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> createAdminWithManageRoomsPermission
     * <Function> Create an admin user and assign a specific permission.
     *
     * @return \App\Models\Admin
     */
    private function createAdminWithManageRoomsPermission(): Admin
    {
        $admin = Admin::factory()->create(['enabled' => true]);
        $permission = Permission::firstOrCreate([
            'name' => 'manage logs',
            'guard_name' => 'admin',
        ]);

        $admin->givePermissionTo($permission);

        return $admin;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> test_admin_logs_management_index
     * <Function> Ensure the admin login page can be rendered successfully.
     *
     * @return void
     */
    public function test_admin_logs_management_index_renders_component(): void
    {
        $admin = $this->createAdminWithManageRoomsPermission();
        $this->actingAs($admin, 'admin');
        // Arrange: mock the service so no DB/JSON SQL is executed
        $this->mock(ActivityLogsService::class, function ($mock) {
            $items = collect([
                (object) [
                    'id'          => 1,
                    'log_name'    => 'information',
                    'causer_type' => 'App\\Models\\User',
                    'description' => 'User logged in',
                    'properties'  => ['code' => 'ABC123'],
                    'created_at'  => now(), // <--- Add this property
                ],
            ]);
            $admin = $this->createAdminWithManageRoomsPermission();

            $paginator = new LengthAwarePaginator(
                $items,
                $items->count(),
                15, // per page
                1,  // current page
                ['path' => '/admin/management/logs']
            );

            $mock->shouldReceive('getLogs')->twice()->andReturn($paginator);
            $mock->shouldReceive('getFilterOptions')->once()->andReturn([
                'levels' => ['info', 'warning', 'error'],
            ]);
        });

        // Act
        $from = now()->startOfMonth()->toDateString();
        $to   = now()->endOfMonth()->addDay()->toDateString();

        $response = $this->followingRedirects()->get('admin/management/logs');

        $response->assertOk();
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('admin/logs/Index')
                ->where('queryParams.date_from', $from)
                ->where('queryParams.date_to', $to)
                ->where('filter.levels', ['info', 'warning', 'error'])
                ->where('logs.data.0.log_name', 'information')
                ->where('logs.data.0.causer_type', 'App\\Models\\User')
                ->where('logs.data.0.description', 'User logged in')
        );
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> test_admin_logs_search_function
     * <Function> Test if the search function is working.
     *
     * @return void
     */
    public function test_admin_logs_search_function_uses_service_and_returns_results(): void
    {
        // Arrange: admin auth
        $admin = $this->createAdminWithManageRoomsPermission();
        $this->actingAs($admin, 'admin');

        // Arrange: mock service with a search term
        $term = 'information';

        $this->mock(ActivityLogsService::class, function ($mock) use ($term) {
            $items = collect([
                (object) [ // <--- Cast to object
                    'id'          => 1,
                    'log_name'    => 'information',
                    'causer_type' => 'App\\Models\\User',
                    'description' => 'User logged in',
                    'properties'  => (object) ['code' => 'INF-001'], // <--- Cast nested if needed
                    'created_at'  => now(), // <--- Add this property
                ],
            ]);

            $paginator = new LengthAwarePaginator(
                $items,
                $items->count(),
                15,
                1,
                ['path' => '/admin/management/logs']
            );

            // Service will get called with the current Request internally; we just return results.
            $mock->shouldReceive('getLogs')->twice()->andReturn($paginator);
            $mock->shouldReceive('getFilterOptions')->once()->andReturn([
                'levels' => ['info', 'warning', 'error'],
            ]);
        });

        // Act
        $from = now()->startOfMonth()->toDateString();
        $to   = now()->endOfMonth()->addDay()->toDateString();

        // include the search param so it appears in queryParams after redirect
        $response = $this->followingRedirects()->get("admin/management/logs?search={$term}");

        $response->assertOk();
        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('admin/logs/Index')
                ->where('queryParams.search', $term)
                ->where('queryParams.date_from', $from)
                ->where('queryParams.date_to', $to)
                ->where('logs.data.0.log_name', 'information')
                ->where('logs.data.0.description', 'User logged in')
                ->where('logs.data.0.properties.code', 'INF-001')
        );
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_admin_logs_export_function
     * <Function> Test if the export function is working.
     *
     * @return void
     */
    public function test_admin_logs_export(): void
    {

        $admin = $this->createAdminWithManageRoomsPermission();

        $this->actingAs($admin, 'admin');

        Excel::fake();

        $this->get(route('admin.management.logs.export'))->assertOk();

        Excel::assertDownloaded('activity_logs_' . now('Asia/Manila')->format('Ymd') . '.xlsx');
    }
}

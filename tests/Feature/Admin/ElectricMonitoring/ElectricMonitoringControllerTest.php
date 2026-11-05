<?php

namespace Tests\Feature\Admin\ElectricMonitoring;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use App\Models\Admin;
use Inertia\Testing\AssertableInertia as Assert;

class ElectricMonitoringControllerTest extends TestCase
{
    use RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // admin_can_view_electric_meters_with_filters                         (1.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> admin_can_view_electric_meters_with_filters
     * <Function> Test if admin can view index with electric meters and filters.
     *
     * @return void
     */

    public function test_admin_can_view_electric_meters_index(): void
    {
        $admin = Admin::factory()->create([
            'enabled'           => 1,
            'email_verified_at' => now(),
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $perm = Permission::firstOrCreate([
            'name'       => 'electric monitoring',
            'guard_name' => 'admin',
        ]);
        $admin->givePermissionTo($perm);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.electric-monitoring.index'));

        $response->assertOk();

        $response->assertInertia(
            fn(Assert $page) => $page
                ->component('admin/electric-monitoring/Index')
                ->has('electric_meter')
                ->has('filters')
        );
    }
}

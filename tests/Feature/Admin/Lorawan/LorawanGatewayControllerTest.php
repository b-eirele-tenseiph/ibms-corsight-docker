<?php

namespace Tests\Feature\Admin\Lorawan;

use App\Models\Admin;
use Tests\TestCase;
use App\Models\Floor;
use App\Models\LorawanGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

class LorawanGatewayControllerTest extends TestCase
{
    use RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // createAdminWithManageRoomsPermission                                (1.0)
    // test_displays_gateway_index                                         (2.0)
    // test_stores_a_gateway                                               (3.0)
    // test_updates_a_gateway                                              (4.0)
    // test_deletes_a_gateway                                              (5.0)
    // test_admin_can_export_gateways                                      (6.0)
    // test_admin_can_import_gateways                                      (7.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> createAdminWithManageRoomsPermission
     * <Function> Create an admin user and assign a specific permission.
     *
     * @return void
     */
    private function createAdminWithManageRoomsPermission(): Admin
    {
        $permission = Permission::firstOrCreate([
            'name' => 'manage lorawan',
            'guard_name' => 'admin',
        ]);

        $admin = Admin::factory()->create(['enabled' => true]);
        $admin->givePermissionTo($permission);

        return $admin;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> test_displays_gateway_index.
     * <Function> Displays the index page and verifies gateway listing with
     *           filters.
     *
     * @return void
     */
    public function test_displays_gateway_index(): void
    {
        $admin = $this->createAdminWithManageRoomsPermission();
        $this->actingAs($admin, 'admin');

        $floor = Floor::factory()->create();
        LorawanGateway::factory()->create([
            'ip_address' => '192.168.1.10',
            'mac_address' => 'AA:BB:CC:DD:EE:FF',
            'connection' => 'milesight',
            'floor_id' => $floor->id,
        ]);

        $response = $this->get(route('admin.management.lorawan.gateways.index', [
            'search' => '192.168.1.10',
            'floor' => $floor->id,
            'field' => 'ip_address',
            'connection' => 'milesight',
            'direction' => 'asc',
        ]));

        $response->assertInertia(
            fn($page) =>
            $page->component('admin/lorawan-gateway/Index')
                ->has('gateways')
                ->has('filters')
                ->has('floors')
        );
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> test_stores_a_gateway.
     * <Function> Stores a new gateway and validates data is persisted.
     *
     * @return void
     */
    public function test_stores_a_gateway(): void
    {
        $admin = $this->createAdminWithManageRoomsPermission();
        $this->actingAs($admin, 'admin');

        $floor = Floor::factory()->create();

        $response = $this->post(route('admin.management.lorawan.gateways.store'), [
            'ip_address' => '10.0.0.1',
            'mac_address' => '00:11:22:33:44:55',
            'connection' => 'milesight',
            'floor_id' => $floor->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('lorawan_gateways', [
            'ip_address' => '10.0.0.1',
            'mac_address' => '00:11:22:33:44:55',
            'connection' => 'milesight',
            'floor_id' => $floor->id,
        ]);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_updates_a_gateway.
     * <Function> Updates an existing gateway and checks database state.
     *
     * @return void
     */
    public function test_updates_a_gateway(): void
    {
        $admin = $this->createAdminWithManageRoomsPermission();
        $this->actingAs($admin, 'admin');

        $gateway = LorawanGateway::factory()->create([
            'ip_address' => '10.0.0.2',
        ]);

        $this->put(route('admin.management.lorawan.gateways.update', $gateway->id), [
            'ip_address' => '10.0.0.3',
            'mac_address' => $gateway->mac_address,
            'connection' => 'kerlink',
            'floor_id' => $gateway->floor_id,
        ])->assertRedirect();

        $account = LorawanGateway::first();

        $this->assertDatabaseHas('lorawan_gateways', [
            'id' => $account->id,
            'ip_address' => $account->ip_address,
        ]);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_deletes_a_gateway.
     * <Function> Deletes a gateway and ensures removal from the database.
     *
     * @return void
     */
    public function test_deletes_a_gateway()
    {
        $admin = $this->createAdminWithManageRoomsPermission();
        $this->actingAs($admin, 'admin');

        $gateway = LorawanGateway::factory()->create();

        $response = $this->delete(route('admin.management.lorawan.gateways.destroy', $gateway));

        $response->assertRedirect();

        $this->assertDatabaseMissing('lorawan_gateways', [
            'id' => $gateway->id,
        ]);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_admin_can_export_gateways.
     * <Function> Exports gateways to an Excel file and checks for download.
     *
     * @return void
     */
    public function test_admin_can_export_gateways(): void
    {
        LorawanGateway::factory()->create();
        $admin = $this->createAdminWithManageRoomsPermission();
        $this->actingAs($admin, 'admin');

        $this->get(route('admin.management.lorawan.gateways.export'))
            ->assertOk()
            ->assertDownload('lorawan_gateways_' . now('Asia/Manila')->format('Ymd') . '.xlsx');
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_admin_can_import_gateways
     * <Function> Imports gateways from an Excel file and checks database state.
     *
     * @return void
     */
    public function test_admin_can_import_gateways(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('gateways.xlsx');
        $admin = $this->createAdminWithManageRoomsPermission();

        $this->actingAs($admin, 'admin')
            ->post('admin.management.lorawan.gateways.import', ['file' => $file]);
        $this->assertCount(0, LorawanGateway::all());
    }
}

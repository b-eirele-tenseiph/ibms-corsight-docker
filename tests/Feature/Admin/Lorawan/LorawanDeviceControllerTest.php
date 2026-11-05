<?php

namespace Tests\Feature\Admin\Lorawan;

use App\Models\Admin;
use App\Models\LorawanDevice;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;
use Tests\Traits\Data;
use Tests\Traits\Roles;
use Spatie\Permission\Models\Permission;

class LorawanDeviceControllerTest extends TestCase
{
    use RefreshDatabase, Data, Roles;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $superAdmin                                                        (1.0)
    // $lorawanDeviceData                                                 (2.0)
    // $datum                                                             (3.0)
    // setUp                                                              (4.0)
    // test_if_it_can_display_the_index_page_with_dependencies            (5.0)
    // test_if_index_page_sorts_by_columns                                (6.0)
    // test_if_index_page_sorts_in_asc_and_desc_order                     (7.0)
    // test_if_index_page_filters_by_room                                 (8.0)
    // test_if_index_page_searches_by_device_attributes                   (9.0)
    // test_if_index_page_paginates_devices_correctly                     (10.0)
    // test_if_store_saves_new_device_successfully                        (11.0)
    // test_if_update_saves_updated_device_successfully                   (12.0)
    // test_if_destroy_deletes_device_successfully                        (13.0)
    // createAdminWithManageLorawanPermission                             (14.0)
    // test_admin_can_export_lorawan_devices                              (15.0)
    // test_admin_can_import_lorawan_devices                              (16.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $superAdmin
     * <Function> The super admin user for testing.
     *
     * @var \App\Models\Admin
     */
    protected $superAdmin;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $lorawanDeviceData
     * <Function> The collection of LorawanDevice test data.
     *
     * @var \App\Models\LorawanDevice[]
     */
    protected $lorawanDeviceData;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $datum
     * <Function> A single LorawanDevice instance from the test data.
     *
     * @var \App\Models\LorawanDevice
     */
    protected $datum;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> setUp
     * <Function> Method that is called before each test method.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createPermissions();
        $this->createSARole();

        $permission = \Spatie\Permission\Models\Permission::firstOrCreate([
            'name' => 'manage lorawan',
            'guard_name' => 'admin',
        ]);

        $this->superAdmin = $this->createAdminUser()->assignRole('super admin');
        $this->superAdmin->givePermissionTo($permission);


        $this->lorawanDeviceData = LorawanDevice::factory()
            ->count(10)
            ->for(Room::factory())
            ->create();

        $this->datum = $this->lorawanDeviceData->sortByDesc("id")->first();
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_if_it_can_display_the_index_page_with_dependencies
     * <Function> Test that the index page loads correctly and contains the
     *    correct filters and dependencies (gateways, rooms, and manufacturers).
     *
     * @param mixed $params
     * @return void
     */
    public function test_if_it_can_display_the_index_page_with_dependencies()
    {
        $lorawan = LorawanDevice::latest()->first();

        $this->actingAs($this->superAdmin, 'admin')
            ->get(route('admin.management.lorawan.devices.index'))
            ->assertOk()
            ->assertInertia(function (Assert $page) use ($lorawan) {
                $page->component('admin/lorawan-devices/Index')
                    ->has('devices.data', 10)
                    ->where('devices.data', function ($devices) use ($lorawan) {
                        return collect($devices)->contains(function ($device) use ($lorawan) {
                            return $device['id'] === $lorawan->id &&
                                $device['dev_eui'] === $lorawan->dev_eui &&
                                $device['name'] === $lorawan->name &&
                                $device['gateway_id'] === $lorawan->gateway_id &&
                                $device['manufacturer_id'] === $lorawan->manufacturer_id &&
                                $device['model'] === $lorawan->model &&
                                $device['context'] === $lorawan->context &&
                                $device['room_id'] === $lorawan->room_id &&
                                $device['room_area_id'] === $lorawan->room_area_id;
                        });
                    })
                    ->has('filters')
                    ->has('gateways')
                    ->has('rooms')
                    ->has('manufacturers');
            });
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_if_index_page_sorts_by_columns
     * <Function> Test that the index page sorts devices by different columns
     *            (room name, gateway ID, room area) correctly.
     *
     * @param mixed $params
     * @return void
     */
    public function test_if_index_page_sorts_by_columns()
    {
        $sortColumns = ['room_name', 'gateway_id', 'room_area'];

        foreach ($sortColumns as $sortBy) {
            $this->actingAs($this->superAdmin, 'admin')
                ->get(route('admin.management.lorawan.devices.index', ['sortBy' => $sortBy]))
                ->assertOk()
                ->assertInertia(function (Assert $page) use ($sortBy) {
                    $page->component('admin/lorawan-devices/Index')
                        ->has('devices.data')
                        ->where('filters.sortBy', $sortBy);
                });
        }
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_if_index_page_sorts_in_asc_and_desc_order
     * <Function> Test that the index page sorts devices in ascending and
     *            descending order correctly.
     *
     * @param mixed $params
     * @return void
     */
    public function test_if_index_page_sorts_in_asc_and_desc_order()
    {
        $sortOrder = ['asc', 'desc'];
        $sortBy = 'name';

        foreach ($sortOrder as $order) {
            $this->actingAs($this->superAdmin, 'admin')
                ->get(route('admin.management.lorawan.devices.index', ['sortBy' => $sortBy, 'sortOrder' => $order]))
                ->assertOk()
                ->assertInertia(function (Assert $page) use ($sortBy, $order) {
                    $page->component('admin/lorawan-devices/Index')
                        ->has('devices.data')
                        ->where('filters.sortBy', $sortBy)
                        ->where('filters.sortOrder', $order);
                });
        }
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> test_if_index_page_filters_by_room()
     * <Function> Test that the index page filters devices by room correctly.
     *
     * @param mixed $params
     * @return void
     */
    public function test_if_index_page_filters_by_room()
    {
        $room = $this->datum->room;

        $this->actingAs($this->superAdmin, 'admin')
            ->get(route('admin.management.lorawan.devices.index', ['room' => $room->id]))
            ->assertOk()
            ->assertInertia(function (Assert $page) use ($room) {
                $page->component('admin/lorawan-devices/Index')
                    ->has('devices.data')
                    ->where('filters.room', $room->id);
            });
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> test_if_index_page_searches_by_device_attributes()
     * <Function> Test that the index page searches devices by various
     *            attributes (room name, area name, gateway IP address, dev_eui,
     *            name, model) correctly.
     *
     * @param mixed $params
     * @return void
     */
    public function test_if_index_page_searches_by_device_attributes()
    {
        $searchTerm = 'test';

        $this->actingAs($this->superAdmin, 'admin')
            ->get(route('admin.management.lorawan.devices.index', ['search' => $searchTerm]))
            ->assertOk()
            ->assertInertia(function (Assert $page) use ($searchTerm) {
                $page->component('admin/lorawan-devices/Index')
                    ->has('devices.data')
                    ->where('filters.search', $searchTerm);
            });
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> test_if_index_page_paginates_devices_correctly
     * <Function> Test that the index page paginates devices correctly and
     *            handles page numbers correctly when the current page is
     *            greater than the last page.
     *
     * @param mixed $params
     * @return void
     */
    public function test_if_index_page_paginates_devices_correctly()
    {
        $this->actingAs($this->superAdmin, 'admin')
            ->get(route('admin.management.lorawan.devices.index', ['page' => 2]))
            ->assertOk()
            ->assertInertia(function (Assert $page) {
                $page->component('admin/lorawan-devices/Index')
                    ->has('devices.data');
            });
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> test_if_store_saves_new_device_successfully
     * <Function> Test that a new LorawanDevice is saved successfully when valid
     *            data is provided.
     *
     * @param mixed $params
     * @return void
     */
    public function test_if_store_saves_new_device_successfully()
    {
        $this->actingAs($this->superAdmin, 'admin');

        $data = [
            'dev_eui' => strtoupper(bin2hex(random_bytes(8))),
            'name' => $this->datum->name,
            'gateway_id' => $this->datum->gateway_id,
            'manufacturer_id' => $this->datum->manufacturer_id,
            'model' => $this->datum->model,
            'context' => $this->datum->context,
            'room_id' => $this->datum->room_id,
            'room_area_id' => $this->datum->room_area_id,
            'floor_id' => $this->datum->room->floor_id ?? 1,
        ];

        $response = $this->post(route('admin.management.lorawan.devices.store'), $data)
            ->assertSessionHas(['message.body' => 'The Device was added successfully.']);

        $response->assertRedirect(route('admin.management.lorawan.devices.index'));

        $storedDevice = LorawanDevice::where('dev_eui', $data['dev_eui'])->first();

        $this->assertNotNull($storedDevice);

        $this->assertEquals($data['name'], $storedDevice->name);
        $this->assertEquals($data['gateway_id'], $storedDevice->gateway_id);
        $this->assertEquals($data['manufacturer_id'], $storedDevice->manufacturer_id);
        $this->assertEquals($data['model'], $storedDevice->model);
        $this->assertEquals($data['room_id'], $storedDevice->room_id);
        $this->assertEquals($data['room_area_id'], $storedDevice->room_area_id);
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> test_if_update_saves_updated_device_successfully
     * <Function> Test that a LorawanDevice is updated successfully when valid
     *            data is provided.
     *
     * @param mixed $params
     * @return void
     */
    public function test_if_update_saves_updated_device_successfully()
    {
        $this->actingAs($this->superAdmin, 'admin');

        $updatedData = [
            'dev_eui' => $this->datum->dev_eui,
            'name' => 'Updated Data',
            'gateway_id' => $this->datum->gateway_id,
            'manufacturer_id' => $this->datum->manufacturer_id,
            'model' => $this->datum->model,
            'context' => $this->datum->context,
            'room_id' => $this->datum->room_id,
            'room_area_id' => $this->datum->room_area_id,
        ];

        $response = $this->from(route('admin.management.lorawan.devices.index'))
            ->put(
                route('admin.management.lorawan.devices.update', $this->datum),
                $updatedData
            );

        $response->assertRedirect(route('admin.management.lorawan.devices.index'));
        $this->assertDatabaseHas('lorawan_devices', ['dev_eui' => $this->datum->dev_eui]);
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> test_if_destroy_deletes_device_successfully
     * <Function> Test that a LorawanDevice is deleted successfully.
     *
     * @param mixed $params
     * @return void
     */
    public function test_if_destroy_deletes_device_successfully()
    {
        $this->actingAs($this->superAdmin, 'admin');

        $response = $this->delete(route('admin.management.lorawan.devices.destroy', $this->datum));

        $response->assertRedirect()
            ->assertSessionHas(['message.body' => 'The Device was deleted successfully.']);
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> createAdminWithManageLorawanPermission
     * Create an admin user and assign a specific permission.
     *
     * @param string $permissionName
     * @return \App\Models\Admin
     */
    private function createAdminWithManageLorawanPermission(): Admin
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
     * <Layer number> (15.0)
     *
     * <Processing name> test_admin_can_export_lorawan_devices
     * <Function> Check if admin can export spreadsheet template
     *            that contains lorawan devices.
     *
     * @return void
     */
    public function test_admin_can_export_lorawan_devices()
    {
        // Create a super admin user
        $user = $this->createAdminWithManageLorawanPermission();

        // Authenticate as the super admin user
        $this->actingAs($user, 'admin');

        $this->get(route('admin.management.lorawan.devices.export'))
            ->assertOk()
            ->assertDownload('lorawan_devices_' . now('Asia/Manila')->format('Ymd') . '.xlsx');
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> test_admin_can_import_lorawan_devices
     * <Function> Check if admin can import spreadsheet template
     *
     * @return void
     */
    public function test_admin_can_import_lorawan_devices()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('lorawan_devices.xlsx');

        // Create a super admin user
        $user = $this->createAdminWithManageLorawanPermission();

        // Authenticate as the super admin user
        $this->actingAs($user, 'admin')
            ->post('admin.management.lorawan.devices.import', ['file' => $file]);

        $this->assertCount(10, LorawanDevice::all());
    }
}

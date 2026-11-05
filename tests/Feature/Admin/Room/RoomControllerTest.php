<?php

namespace Tests\Feature\Admin\Room;

use App\Models\Admin;
use App\Models\Room;
use App\Models\Floor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RoomControllerTest extends TestCase
{
    use RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // createAdminWithManageRoomsPermission                                (1.0)
    // test_it_displays_the_room_index_page                                (2.0)
    // test_it_can_store_a_new_room                                        (3.0)
    // test_it_can_update_an_existing_room                                 (4.0)
    // test_it_can_delete_a_room                                           (5.0)

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
        $permission = Permission::firstOrCreate([
            'name' => 'manage rooms',
            'guard_name' => 'admin',
        ]);

        $admin = Admin::factory()->create(['enabled' => true]);
        $admin->givePermissionTo($permission);

        return $admin;
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> test_it_displays_the_room_index_page.
     * <Function> Displays the index page and verifies room name.
     *
     * @return void
     */
    public function test_displays_the_room_index_page(): void
    {
        $admin = $this->createAdminWithManageRoomsPermission();
        $this->actingAs($admin, 'admin');

        $floor = Floor::factory()->create();
        $room = Room::factory()->create(['floor_id' => $floor->id]);

        $response = $this->get(route('admin.management.rooms.index'));

        $response->assertStatus(200);
        $response->assertSee($room->name);
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> test_it_can_store_a_new_room.
     * <Function> Stores a new room and related data (floor, area).
     *
     * @return void
     */
    public function test_can_store_a_new_room()
    {
        $admin = $this->createAdminWithManageRoomsPermission();
        $this->actingAs($admin, 'admin');

        $data = [
            'name' => 'Test Room',
            'number' => '101',
            'floor_name' => 'First Floor',
            'room_area' => ['Area1', 'Area2'],
            'external_apps' => ['LG', 'AXIS'],
        ];

        $response = $this->post(route('admin.management.rooms.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('rooms', ['name' => 'Test Room', 'number' => '101']);
        $this->assertDatabaseHas('floors', ['name' => 'First Floor']);
        $this->assertDatabaseHas('room_areas', ['name' => 'Area1']);
        $this->assertDatabaseHas('room_areas', ['name' => 'Area2']);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_it_can_update_an_existing_room.
     * <Function> Updates room details and verifies changes.
     *
     * @return void
     */
    public function test_can_update_an_existing_room()
    {
        $admin = $this->createAdminWithManageRoomsPermission();
        $this->actingAs($admin, 'admin');

        $floor = Floor::factory()->create();
        $room = Room::factory()->create(['floor_id' => $floor->id]);

        $response = $this->put(route('admin.management.rooms.update', $room), [
            'name' => 'Updated Room',
            'number' => '102',
            'floor_name' => 'New Floor',
            'room_area' => [['id' => null, 'name' => 'Updated Area'], ['id' => null, 'name' => 'Area3']],
            'external_apps' => ['ELECTROLUX'],
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'name' => 'Updated Room',
            'number' => '102',
        ]);

        $this->assertDatabaseHas('floors', ['name' => 'New Floor']);
        $this->assertDatabaseHas('room_areas', ['name' => 'Updated Area']);
        $this->assertDatabaseHas('room_areas', ['name' => 'Area3']);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_it_can_delete_a_room.
     * <Function> Deletes a room and checks database state.
     *
     * @return void
     */
    public function test_can_delete_a_room()
    {
        $admin = $this->createAdminWithManageRoomsPermission();
        $this->actingAs($admin, 'admin');

        // Create floor and room
        $floor = Floor::factory()->create();
        $room = Room::factory()->create(['floor_id' => $floor->id]);

        $this->assertDatabaseHas('rooms', ['id' => $room->id]);

        // Perform DELETE request using route helper with model binding
        $response = $this->delete(route('admin.management.rooms.destroy', $room));

        // Assert redirect and session flash
        $response->assertRedirect(route('admin.management.rooms.index'));

        // Ensure room is actually deleted
        $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
    }
}

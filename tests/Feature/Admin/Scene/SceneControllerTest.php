<?php

namespace Tests\Feature\Admin\Scene;

use App\Models\LorawanDevice;
use App\Models\Room;
use App\Models\RoomAccess;
use App\Models\Scene;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Traits\Data;
use Tests\Traits\Roles;

class SceneControllerTest extends TestCase
{
    use Data, Roles, RefreshDatabase, WithFaker;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // setUp                                                              (1.0)
    // createSuperAdminUserWithPermission                                 (2.0)
    // createSuperAdminUserWithoutPermission                              (3.0)
    // createGuestUserWithRoomAccess                                      (4.0)
    // createUserWithRoomAndPermission                                    (5.0)
    // test_super_admin_has_access_to_scene_management                    (6.0)
    // test_super_admin_without_permission_cant_access_scene_management   (7.0)
    // test_admin_can_store_scene                                         (8.0)
    // test_admin_can_update_scene_with_valid_data                        (9.0)
    // test_admin_can_delete_scene                                        (10.0)
    // test_user_has_access_to_guest_dashboard                            (11.0)
    // test_guest_can_store_scene                                         (12.0)
    // test_guest_can_update_scene_with_valid_data                        (13.0)
    // test_guest_can_delete_scene                                        (14.0)

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

        $this->createPermissions();
        $this->createSARole();
        $this->createAdminRole();
        $this->createStaffRole();
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> createSuperAdminUserWithPermission
     * <Function> Creation of super admin account with permission
     *
     * @return mixed
     */
    private function createSuperAdminUserWithPermission(): mixed
    {
        return $this->createAdminUser()->assignRole('super admin');
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> createSuperAdminUserWithoutPermission
     * <Function> Creation of super admin account without permission
     *
     * @return mixed
     */
    private function createSuperAdminUserWithoutPermission(): mixed
    {
        return $this->createAdminUser()->assignRole('admin');
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> createGuestUserWithRoomAccess
     * <Function> Creation of guest account with room access
     *
     * @return mixed
     */
    private function createGuestUserWithRoomAccess(): array
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['number' => 314]);

        RoomAccess::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'is_permanent' => true,
        ]);

        return ['user' => $user, 'room' => $room];
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> createUserWithRoomAndPermission
     * <Function> Creation of user account with room access and permission
     *
     * @return mixed
     */
    private function createUserWithRoomAndPermission(): mixed
    {
        return $this->createUserWithRoomAndPermission()->assignRole('guest');
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_super_admin_has_access_to_scene_management
     * <Function> Test super admin has access to scene management
     *
     * @return void
     */
    public function test_super_admin_has_access_to_scene_management(): void
    {
        $superAdmin = $this->createSuperAdminUserWithPermission();

        $response = $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.management.settings.scenes.index'));

        $response->assertStatus(200);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_super_admin_without_permission_cant_access_scene_management
     * <Function> Test super admin without permission can't access scene management
     *
     * @return void
     */
    public function test_super_admin_without_permission_cant_access_scene_management(): void
    {
        $superAdmin = $this->createSuperAdminUserWithoutPermission();

        $response = $this->actingAs($superAdmin, 'admin')
            ->get(route('admin.management.settings.scenes.index'));

        $response->assertForbidden();
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> test_admin_can_store_scene
     * <Function> Test admin can store scene
     *
     * @return void
     */
    public function test_admin_can_store_scene(): void
    {

        $room = Room::factory()->create();
        $device = LorawanDevice::factory()->create();

        $superAdmin = $this->createSuperAdminUserWithPermission();
        $this->actingAs($superAdmin, 'admin');

        $sceneData = [
            'name' => 'New Morning Scene',
            'room_id' => $room->id,
            'scheduled' => false,
            'color' => '#FFFFFF',
            'icon' => 'sun',
            'commands' => [
                [
                    'id' => $device->id,
                    'command' => ['command' => 'turnOn'],
                    'type' => 'switch_1',
                ]
            ],
            'scheduleType' => 'custom',
            'days' => ["Monday"],
            'time' => "12:00",
        ];

        // Act: Post the data to the store route.
        $response = $this->post(route('scenes.store'), $sceneData);

        // Assert: Check for a redirect and a success flash message.
        $response->assertRedirect();
        $this->assertDatabaseHas('scenes', [
            'name' => $sceneData['name'],
            'room_id' => $room->id,
            'scheduled' => $sceneData['scheduled'],
        ]);

        $this->assertDatabaseHas('scene_devices', [
            'scene_id' => Scene::first()->id,
            'name' => $device->name,
            'command_type' => $sceneData['commands'][0]['type'],
            'command->command' => $sceneData['commands'][0]['command']['command'],
        ]);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> test_admin_can_update_scene_with_valid_data
     * <Function> Test admin can update scene with valid data
     *
     * @return void
     */
    public function test_admin_can_update_scene_with_valid_data(): void
    {
        $room = Room::factory()->create();
        $device = LorawanDevice::factory()->create();

        $superAdmin = $this->createSuperAdminUserWithPermission();
        $this->actingAs($superAdmin, 'admin');

        $sceneData = [
            'name' => 'New Morning Scene',
            'room_id' => $room->id,
            'scheduled' => false,
            'color' => '#FFFFFF',
            'icon' => 'sun',
            'commands' => [
                [
                    'id' => $device->id,
                    'command' => ['command' => 'turnOn'],
                    'type' => 'switch_1',
                ]
            ],
            'scheduleType' => 'custom',
            'days' => ["Monday"],
            'time' => "12:00",
        ];

        $response = $this->post(route('scenes.store'), $sceneData);

        $updateData = [
            'name' => 'Updated Scene Name',
            'room_id' => $room->room_id,
            'scheduled' => false,
            'color' => '#F0F0F0F0',
            'icon' => 'BulbOffIcon',
            'commands' => [
                [
                    'id' => $device->id,
                    'command' => ['command' => 'turnOff'],
                    'type' => 'switch_1',
                ]
            ],
            'scheduleType' => 'scheduled',
            'days' => ["Monday"],
            'time' => "12:00",
        ];

        // $response = $this->put(route('scenes.update', $sceneData), $updateData);
        $response = $this->put(route('scenes.update', Scene::first()->id), $updateData);

        // Assert: Check for a redirect and a success flash message.
        $response->assertRedirect();
        $this->assertDatabaseHas('scenes', [
            'name' => $sceneData['name'],
            'room_id' => $room->id,
            'scheduled' => $sceneData['scheduled'],
        ]);

        $this->assertDatabaseHas('scene_devices', [
            'scene_id' => Scene::first()->id,
            'name' => $device->name,
            'command_type' => $sceneData['commands'][0]['type'],
            'command->command' => $sceneData['commands'][0]['command']['command'],
        ]);
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> test_admin_can_delete_scene
     * <Function> Test admin can delete scene
     *
     * @return void
     */
    public function test_admin_can_delete_scene(): void
    {
        $scene = Scene::factory()->create();

        $superAdmin = $this->createSuperAdminUserWithPermission();
        $this->actingAs($superAdmin, 'admin');

        $response = $this->delete(route('scenes.destroy', $scene));

        $response->assertRedirect();
        $this->assertDatabaseMissing('scenes', [
            'id' => $scene->id,
        ]);
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> test_user_has_access_to_guest_dashboard
     * <Function> Test user has access to guest dashboard
     *
     * @return void
     */
    public function test_user_has_access_to_guest_dashboard(): void
    {
        ['user' => $guest, 'room' => $room] = $this->createGuestUserWithRoomAccess();
        $response = $this->actingAs($guest, 'web')
            ->get("/user/{$room->number}/dashboard");

        $response->assertOk();
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> test_guest_can_store_scene
     * <Function> Test guest can store scene
     *
     * @return void
     */
    public function test_guest_can_store_scene(): void
    {
        ['user' => $guest, 'room' => $room] = $this->createGuestUserWithRoomAccess();
        $guest->givePermissionTo('control devices');
        $device = LorawanDevice::factory()->create();

        $sceneData = [
            'name' => 'New Morning Scene',
            'room_id' => $room->id,
            'scheduled' => false,
            'color' => '#FFFFFF',
            'icon' => 'sun',
            'commands' => [
                [
                    'id' => $device->id,
                    'command' => ['command' => 'turnOn'],
                    'type' => 'switch_1',
                ]
            ],
            'scheduleType' => 'custom',
            'days' => ["Monday"],
            'time' => "12:00",
        ];

        $this->actingAs($guest, 'web')
            ->get(route('user.dashboard.index', ['room' => $room->number]));

        $response = $this->actingAs($guest, 'web')
            ->post(route('scenes.store'), $sceneData);

        $response->assertRedirect(route('user.dashboard.index', ['room' => $room->number]));

        $this->assertDatabaseHas('scenes', [
            'name' => $sceneData['name'],
            'room_id' => $room->id,
            'scheduled' => $sceneData['scheduled'],
        ]);

        $this->assertDatabaseHas('scene_devices', [
            'scene_id' => Scene::first()->id,
            'name' => $device->name,
            'command_type' => $sceneData['commands'][0]['type'],
            'command->command' => $sceneData['commands'][0]['command']['command'],
        ]);
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> test_guest_can_update_scene_with_valid_data
     * <Function> Test guest can update scene with valid data
     *
     * @return void
     */
    public function test_guest_can_update_scene_with_valid_data(): void
    {
        ['user' => $guest, 'room' => $room] = $this->createGuestUserWithRoomAccess();
        $guest->givePermissionTo('control devices');
        $device = LorawanDevice::factory()->create();

        $sceneData = [
            'name' => 'New Morning Scene',
            'room_id' => $room->id,
            'scheduled' => false,
            'color' => '#FFFFFF',
            'icon' => 'sun',
            'commands' => [
                [
                    'id' => $device->id,
                    'command' => ['command' => 'turnOn'],
                    'type' => 'switch_1',
                ]
            ],
            'scheduleType' => 'custom',
            'days' => ["Monday"],
            'time' => "12:00",
        ];

        $this->actingAs($guest, 'web')
            ->get(route('user.dashboard.index', ['room' => $room->number]));

        $response = $this->post(route('scenes.store'), $sceneData);

        $updateData = [
            'name' => 'Updated Scene Name',
            'room_id' => $room->room_id,
            'scheduled' => false,
            'color' => '#F0F0F0F0',
            'icon' => 'BulbOffIcon',
            'commands' => [
                [
                    'id' => $device->id,
                    'command' => ['command' => 'turnOff'],
                    'type' => 'switch_1',
                ]
            ],
            'scheduleType' => 'scheduled',
            'days' => ["Monday"],
            'time' => "12:00",
        ];

        $response = $this->actingAs($guest, 'web')
            ->put(route('scenes.update', Scene::first()->id), $updateData);

        $response->assertRedirect(route('user.dashboard.index', ['room' => $room->number]));

        $this->assertDatabaseHas('scenes', [
            'name' => $sceneData['name'],
            'room_id' => $room->id,
            'scheduled' => $sceneData['scheduled'],
        ]);

        $this->assertDatabaseHas('scene_devices', [
            'scene_id' => Scene::first()->id,
            'name' => $device->name,
            'command_type' => $sceneData['commands'][0]['type'],
            'command->command' => $sceneData['commands'][0]['command']['command'],
        ]);
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> test_guest_can_delete_scene
     * <Function> Test guest can delete scene
     *
     * @return void
     */
    public function test_guest_can_delete_scene(): void
    {

        $scene = Scene::factory()->create();
        ['user' => $guest, 'room' => $room] = $this->createGuestUserWithRoomAccess();
        $guest->givePermissionTo('control devices');

        $this->actingAs($guest, 'web')
            ->get(route('user.dashboard.index', ['room' => $room->number]));

        $response = $this->delete(route('scenes.destroy', $scene));

        $response->assertRedirect();
        $this->assertDatabaseMissing('scenes', [
            'id' => $scene->id,
        ]);
    }
}

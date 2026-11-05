<?php

namespace Tests\Feature\Staff\Dashboard;

use App\Http\Controllers\Staff\Dashboard\DashboardController;
use App\Models\Room;
use App\Models\RoomAccess;
use App\Models\User;
use Illuminate\Foundation\Testing\{RefreshDatabase, WithFaker};
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\{Data, Roles};
use App\Services\RoomAccess\RoomAccessService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Mockery\MockInterface;
use Spatie\Permission\Models\Permission;

class DashboardControllerTest extends TestCase
{
    use Data, Roles, RefreshDatabase, WithFaker;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // setUp                                                              (1.0)
    // createStaffUser                                                    (2.0)
    // accessStaffDashboard                                               (3.0)
    // mockAccessesMethod                                                 (4.0)
    // mockLoadRoomMethod                                                 (5.0)
    // mockSaveAccessMethod                                               (6.0)
    // mockUpdateAccessMethod                                             (7.0)
    // mockDeleteAccessMethod                                             (8.0)
    // createUser                                                         (9.0)
    // createAccessData                                                   (10.0)
    // storeGuestUserWithRoomAccess                                       (11.0)
    // updateGuestUserWithRoomAccess                                      (12.0)
    // deleteGuestUserWithRoomAccess                                      (13.0)
    // createGuestUserDataWithDefaultUsername                             (14.0)
    // test_staff_role_has_permissions_to_manage_room_access              (15.0)
    // test_staff_role_with_permission_can_access_staff_dashboard         (16.0)
    // test_admin_without_permission_cant_access_staff_dashboard          (17.0)
    // test_staff_can_access_front_desk_dashboard_room_accesses           (18.0)
    // test_loading_rooms_along_with_its_status_and_room_accesses         (19.0)
    // test_storing_guest_user_with_permanent_room_access                 (20.0)
    // test_storing_guest_user_function_contain_validation_rules          (21.0)
    // test_storing_guest_user_function_contain_validation_if_data_       (22.0)
    // are_not_valid
    // test_updating_guest_user_with_permanent_room_access                (23.0)
    // test_updating_guest_user_with_no_permanent_room_access             (24.0)
    // test_deleting_guest_user_with_room_access                          (25.0)

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

        // Create necessary roles and permissions
        $this->createPermissions();
        $this->createAdminRole();
        $this->createStaffRole();
        $this->createUserRole();
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> createStaffUser
     * <Function> Creation of admin account with staff role
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    private function createStaffUser(): Model
    {
        $staff = $this->createAdminUser()->assignRole('staff');

        // Ensure permission exists
        Permission::findOrCreate('manage room access', 'admin');

        // Assign permission to the user
        $staff->givePermissionTo('manage room access');

        return $staff;
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> accessStaffDashboard
     * <Function> Functions that access the staff dashboard.
     *
     * @return \Illuminate\Testing\TestResponse
     */
    private function accessStaffDashboard()
    {
        // Create staff user
        $staff = $this->createAdminUser()->assignRole('staff');

        // Ensure 'manage room access' permission exists
        Permission::findOrCreate('manage room access', 'admin');

        // Assign permission directly to the user
        $staff->givePermissionTo('manage room access');

        // Act as this staff and access the dashboard
        return $this->actingAs($staff, 'admin')
            ->get(action([DashboardController::class, 'index']))
            ->assertStatus(200)
            ->assertInertia(function (Assert $page) {
                $page->component('staff/dashboard/Index');
            });
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> mockAccessesMethod
     * <Function> Simulates access methods, collection of rooms with their
     *            room accesses and users.
     *
     * @return void
     */
    private function mockAccessesMethod(): void
    {
        $paginator = new LengthAwarePaginator(
            items: new Collection(), // Empty data
            total: 0,
            perPage: 15,
            currentPage: 1,
            options: ['path' => request()->url(), 'query' => request()->query()]
        );

        $this->instance(
            RoomAccessService::class,
            $this->partialMock(RoomAccessService::class, function (MockInterface $mock) use ($paginator) {
                $mock->shouldReceive('accesses')
                    ->once()
                    ->andReturn($paginator);
            })
        );
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> mockLoadRoomMethod
     * <Function> Simulates the process of loading room data while applying
     *            specific filters.
     *
     * @param mixed $room
     * @param array $filters
     *
     * @return void
     */
    private function mockLoadRoomMethod($room, array $filters): void
    {
        $this->app->instance(
            RoomAccessService::class,
            $this->partialMock(RoomAccessService::class, function (MockInterface $mock) use ($room, $filters) {
                $mock->shouldReceive('loadRoom')
                    ->once()
                    ->andReturn($room, $filters);
            })
        );
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> mockSaveAccessMethod
     * <Function> Simulates the process of assigning room access to a user
     *            when the user is created.
     *
     * @param mixed $user
     * @param mixed $room
     *
     * @return void
     */
    private function mockSaveAccessMethod(array $roomAccessData, $room): void
    {
        $this->app->instance(
            RoomAccessService::class,
            $this->partialMock(RoomAccessService::class, function (MockInterface $mock) use ($roomAccessData, $room) {
                $mock->shouldReceive('createRoomAccess')
                    ->with($roomAccessData, $room)
                    ->andReturn(true);
            })
        );
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> mockUpdateAccessMethod
     * <Function> Simulates the process of updating the room access to a user
     *            when the user is updated.
     *
     * @param mixed $user
     * @param mixed $roomAccess
     *
     * @return void
     */
    private function mockUpdateAccessMethod($user, $roomAccess): void
    {
        $this->app->instance(
            RoomAccessService::class,
            $this->partialMock(RoomAccessService::class, function (MockInterface $mock) use ($roomAccess, $user) {
                $mock->shouldReceive('updateRoomAccess')
                    ->withArgs(function ($request, $access) use ($roomAccess) {
                        return $access->id === $roomAccess->id;
                    })
                    ->andReturn($user, $roomAccess);
            })
        );
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> mockDeleteAccessMethod
     * <Function> Simulates the process of deleting the room access to a user
     *            when the user is updated.
     *
     * @param mixed $user
     * @param mixed $roomAccess
     *
     * @return void
     */
    private function mockDeleteAccessMethod($user, $roomAccess): void
    {
        $this->app->instance(
            RoomAccessService::class,
            $this->partialMock(RoomAccessService::class, function (MockInterface $mock) use ($roomAccess, $user) {
                $mock->shouldReceive('deleteAccess')
                    ->andReturn($user, $roomAccess);
            })
        );
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> createUser
     * <Function> Create new user.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function createUser(): Model
    {
        //Create room and user
        $room = Room::factory()->createOne();

        return User::factory()->for($room, 'room')
            ->state(['password' => bcrypt('password')])
            ->createOne();
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> createAccessData
     * <Function> Generate room access data for form input.
     *
     * @param bool $isPermanent
     * @param array $data
     *
     * @return array
     */
    private function createAccessData(bool $isPermanent, array $data = []): array
    {
        $accessData = [
            "username" => $data['username'] ?? $this->faker->unique()->word(),
            "name" => $data['name'] ?? $this->faker->unique()->word(),
            "pin" => $data['pin'] ?? $this->faker->randomNumber(6, true),
            "is_permanent" => $data['is_permanent'] ?? $isPermanent,
            "email" => $data['email'] ?? $this->faker->unique()->safeEmail(),
        ];

        if (!$accessData['is_permanent']) {
            $accessData = array_merge($accessData, [
                "start_date" => $data['start_date'] ?? now()->format('Y-m-d'),
                "end_date" => $data['end_date'] ?? now()->addDay()->format('Y-m-d'),
                "start_time" => $data['start_time'] ?? '12:00:00',
                "end_time" => $data['end_time'] ?? '14:00:00',
            ]);
        }

        return $accessData;
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> storeGuestUserWithRoomAccess
     * <Function> Store a new guest user with access to a specific room.
     *
     * @param bool $isPermanent
     *
     * @return void
     */
    private function storeGuestUserWithRoomAccess(bool $isPermanent): void
    {
        $this->mockAccessesMethod();
        $this->accessStaffDashboard();

        $room = Room::factory()->createOne();
        $data = $this->createAccessData($isPermanent);

        $this->mockSaveAccessMethod($data, $room);

        $this->post(route('staff.dashboard.room-access-new', $room->id), $data)
            ->assertSessionHasAll(['message.body' => 'The Room Access was added successfully.'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> updateGuestUserWithRoomAccess
     * <Function> Update the access details for a guest user who has room access.
     *
     * @param bool $isPermanent
     * @param array $accessData
     *
     * @return void
     */
    private function updateGuestUserWithRoomAccess(bool $isPermanent, array $accessData): void
    {
        $this->mockAccessesMethod();
        $this->accessStaffDashboard();

        $room = Room::factory()->createOne();
        $existUser = User::factory()->createOne();
        $existRoomAccess = RoomAccess::factory()->state([
            'user_id' => $existUser->id,
            'room_id' => $room->id,
            'is_permanent' => $isPermanent,
            'start_date' => $isPermanent ? null : now(),
            'end_date' => $isPermanent ? null : now()->addDay(),
        ])->createOne();

        $this->mockUpdateAccessMethod($existUser, $existRoomAccess);

        $this->put(route('staff.dashboard.room-access-update', $existRoomAccess->id), $this->createAccessData(1, $accessData))
            ->assertSessionHasAll(['message.body' => 'The Room Access was added successfully.'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();
    }

    /**
     * <Layer number> (13.0)
     *
     * <Processing name> deleteGuestUserWithRoomAccess
     * <Function> Delete a guest user who has access to a room.
     *
     * @param bool $isPermanent
     *
     * @return void
     */
    private function deleteGuestUserWithRoomAccess(bool $isPermanent): void
    {
        $this->mockAccessesMethod();
        $this->accessStaffDashboard();

        $room = Room::factory()->createOne();
        $existUser = User::factory()->createOne();
        $roomAccess = RoomAccess::factory()->state([
            'user_id' => $existUser->id,
            'room_id' => $room->id,
            'is_permanent' => $isPermanent,
            'start_date' => $isPermanent ? null : now(),
            'end_date' => $isPermanent ? null : now()->addDay(),
        ])->createOne();

        $this->mockDeleteAccessMethod($existUser, $roomAccess);

        $this->delete(route('staff.dashboard.room-access-delete', $roomAccess->id))
            ->assertSessionHasAll(['message.body' => 'The Room Access was deleted successfully.'])
            ->assertRedirect()
            ->assertSessionHasNoErrors();
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> createGuestUserDataWithDefaultUsername
     * <Function> Generate a guest user data for form input with default username
     *
     * @return \App\Models\User
     */
    private function createGuestUserDataWithDefaultUsername(): Model
    {
        return User::factory()
            ->state(
                ['username' => 'testing'],
            )
            ->createOne();
    }


    /**
     * <Layer number> (15.0)
     *
     * <Processing name> test_staff_role_has_permissions_to_manage_room_access
     * <Function> Check if the staff has manage room access.
     *
     * @return void
     */
    public function test_staff_role_has_permissions_to_manage_room_access(): void
    {
        $role = Role::where('name', 'staff')->first();

        $this->assertNotNull($role);

        $this->assertTrue($role->hasPermissionTo('manage room access'));
    }

    /**
     * <Layer number> (16.0)
     *
     * <Processing name> test_staff_role_with_permission_can_access_staff_dashboard
     * <Function> Check if the authenticated user can access the staff dashboard.
     *
     * @return void
     */
    public function test_staff_role_with_permission_can_access_staff_dashboard(): void
    {
        $this->actingAs($this->createStaffUser(), 'admin')
            ->get(action([DashboardController::class, 'index']))
            ->assertOk()
            ->assertInertia(function (Assert $page) {
                $page->component('staff/dashboard/Index');
            });
    }

    /**
     * <Layer number> (17.0)
     *
     * <Processing name> test_admin_without_permission_cant_access_staff_dashboard
     * <Function> Check if the authenticated user cannot access the staff dashboard.
     *
     * @return void
     */
    public function test_admin_without_permission_cant_access_staff_dashboard(): void
    {
        $this->actingAs($this->createAdminUser()
            ->assignRole('admin'), 'admin')
            ->get(action([DashboardController::class, 'index']))
            ->assertForbidden();
    }

    /**
     * <Layer number> (18.0)
     *
     * <Processing name> test_staff_can_access_front_desk_dashboard_room_accesses
     * <Function> Check if the authenticated user can view front desk dashboard.
     *
     * @return void
     */
    public function test_staff_can_access_front_desk_dashboard_room_accesses(): void
    {
        $this->mockAccessesMethod();
        $this->accessStaffDashboard();
    }

    /**
     * <Layer number> (19.0)
     *
     * <Processing name> test_loading_rooms_along_with_its_status_and_room_accesses
     * <Function> A function that processes the loading of an existing room, along
     *            with its status and related room accesses, according to the
     *            current date filter.
     *
     * @return void
     */
    public function test_loading_rooms_along_with_its_status_and_room_accesses(): void
    {
        $this->mockAccessesMethod();
        $this->accessStaffDashboard();

        $filters = ['date' => now()->format('d/m/Y')];
        $room    = Room::factory()->createOne();

        $this->mockLoadRoomMethod($room, $filters);

        $this->post(route('staff.dashboard.room-access', ['room' => $room->id, 'date' => $filters['date']]));
    }

    /**
     * <Layer number> (20.0)
     *
     * <Processing name> test_storing_guest_user_with_permanent_room_access
     * <Function> Check if the authenticated admin can create guest with
     *            permanent access.
     *
     * @return void
     */
    public function test_storing_guest_user_with_permanent_room_access(): void
    {
        $this->storeGuestUserWithRoomAccess(true);
    }

    /**
     * <Layer number> (21.0)
     *
     * <Processing name> test_storing_guest_user_function_contain_validation_rules
     * <Function> Check if the creation of guest without input data will
     *            generate a validation errors.
     *
     * @return void
     */
    public function test_storing_guest_user_function_contain_validation_rules(): void
    {
        $room = Room::factory()->createOne();

        $response = $this->actingAs($this->createStaffUser(), 'admin')
            ->post(action([DashboardController::class, 'store'], $room->id));

        $response->assertSessionHasErrors(['username', 'name', 'pin']);
    }

    /**
     * <Layer number> (22.0)
     *
     * <Processing name> test_storing_guest_user_function_contain_validation_if_data_are_not_valid
     * <Function> Validator checker for creating a guest account.
     *
     * @return void
     */
    public function test_storing_guest_user_function_contain_validation_if_data_are_not_valid(): void
    {
        $room = Room::factory()->createOne();

        // authenticate the given account by passing the admin guard
        $response = $this->actingAs($this->createStaffUser(), 'admin');

        // checking of input fields without input data
        $response->post(action([DashboardController::class, 'store'], $room->id), [])->assertSessionHasErrors();

        // checking of password input length
        $response->post(action([DashboardController::class, 'store'], $room->id), $this->createAccessData(true, ['pin' => '156']))
            ->assertSessionHasErrors(['pin' => 'The pin field must be 6 digits.']);

        // checking of unique username field
        $this->createGuestUserDataWithDefaultUsername();

        $response->post(action([DashboardController::class, 'store'], $room->id), $this->createAccessData(true, ['username' => 'testing']))
            ->assertSessionHasErrors(['username' => 'The username has already been taken.']);
    }

    /**
     * <Layer number> (23.0)
     *
     * <Processing name> test_updating_guest_user_with_permanent_room_access
     * <Function> Check if the authenticated admin can update guest with
     *            permanent access.
     *
     * @return void
     */
    public function test_updating_guest_user_with_permanent_room_access(): void
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();
        $roomAccess = RoomAccess::factory()->create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'is_permanent' => true,
        ]);

        $accessData = [
            'username' => 'updatedusername',
            'name' => 'Updated User',
            'pin' => 987654,
            'email' => 'updated.email@example.com',
            'is_permanent' => true,
            'room_id' => $room->id,
        ];

        $this->mockUpdateAccessMethod($user, $roomAccess);

        $response = $this->actingAs($this->createStaffUser(), 'admin')
            ->put(route('staff.dashboard.room-access-update', $roomAccess->id), $accessData);

        $response->assertRedirect()
            ->assertSessionHasNoErrors();
    }

    /**
     * <Layer number> (24.0)
     *
     * <Processing name> test_updating_guest_user_with_no_permanent_room_access
     * <Function> Check if the authenticated admin can update guest with
     *            no permanent access.
     *
     * @return void
     */
    public function test_updating_guest_user_with_no_permanent_room_access(): void
    {
        $room = Room::factory()->create();
        $user = User::factory()->create();
        $roomAccess = RoomAccess::factory()->create([
            'user_id'      => $user->id,
            'room_id'      => $room->id,
            'is_permanent' => true,
        ]);

        $accessData = [
            'username'     => 'updatedusername',
            'name'         => 'Updated User',
            'pin'          => 987654,
            'email'        => 'updated.email@example.com',
            'is_permanent' => false,
            'room_id'      => $room->id,

            // ensure start is always in the future
            'start_date'   => now()->addDay()->toDateString(),
            'end_date'     => now()->addDays(3)->toDateString(),

            'start_time'   => '16:00',
            'end_time'     => '17:00',
        ];

        $this->mockUpdateAccessMethod($user, $roomAccess);

        $response = $this->actingAs($this->createStaffUser(), 'admin')
            ->put(route('staff.dashboard.room-access-update', $roomAccess->id), $accessData);

        $response
            ->assertSessionHas('message.body', 'The Room Access was updated successfully.')
            ->assertRedirect()
            ->assertSessionHasNoErrors();
    }

    /**
     * <Layer number> (25.0)
     *
     * <Processing name> test_deleting_guest_user_with_room_access
     * <Function> Check if the authenticated admin can delete guest.
     *
     * @return void
     */
    public function test_deleting_guest_user_with_room_access(): void
    {
        $this->deleteGuestUserWithRoomAccess(true);
    }
}

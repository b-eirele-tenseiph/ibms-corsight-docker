<?php

namespace Tests\Feature\Admin\UserAccount;

use App\Models\Admin;
use App\Models\Room;
use App\Models\RoomAccess;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Traits\Data;
use Tests\Traits\Roles;

use Illuminate\Support\Facades\Notification;
use App\Notifications\AdminNotification;

class UserAccountControllerTest extends TestCase
{
    use Data, Roles, RefreshDatabase, WithFaker;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $adminAccount                                                       (1.0)
    // setUp                                                               (2.0)
    // createAndLoginAdmin                                                 (3.0)
    // test_index_displays_users_with_pagination                           (4.0)
    // test_store_creates_user                                             (5.0)
    // test_update_user                                                    (6.0)
    // test_destroy_user                                                   (7.0)
    // test_admin_can_export_users                                         (8.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $adminAccount
     * <Function> Holds the currently authenticated admin account for testing.
     *
     * @var \App\Models\Admin
     */
    protected $adminAccount;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> setUp
     * <Function> Prepare the test environment and authenticate as admin before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAndLoginAdmin();
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> createAndLoginAdmin
     * <Function> Create and authenticate an admin account for testing.
     *
     * @return void
     */
    protected function createAndLoginAdmin()
    {

        $permission = Permission::firstOrCreate([
            'name' => 'manage user accounts',
            'guard_name' => 'admin',
        ]);

        $this->adminAccount = Admin::factory()->create(['enabled' => true]);
        $this->adminAccount->givePermissionTo($permission);

        $this->actingAs($this->adminAccount, 'admin');
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_index_displays_users_with_pagination
     * <Function> Test that the user management page displays paginated user records.
     *
     * @return void
     */
    public function test_index_displays_users_with_pagination()
    {
        $response = $this->get(route('admin.management.accounts.users.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('admin/user-account/Index')
                ->has('users.data')
        );
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_store_creates_user
     * <Function> Test that a new user can be created and stored in the database.
     *
     * @return void
     */
    public function test_store_creates_user()
    {
        // Initialize Notification Fake at the very start
        Notification::fake();

        $this->seed(RolesAndPermissionsSeeder::class);

        $room = Room::factory()->create();
        $now = now('Asia/Manila');

        $start = $now->copy()->addMinutes(10);
        $end   = $start->copy()->addDays(30);

        $payload = [
            'room_id'              => $room->id,
            'name'                 => 'Test User',
            'username'             => 'testuser',
            'email'                => 'testuser@example.com',
            'password'             => '123456',
            'password_displayable' => '123456',
            'is_permanent'         => false,
            'start_date'           => $start->format('Y-m-d'),
            'end_date'             => $end->format('Y-m-d'),
            'start_time'           => $start->format('H:i:s'),
            'end_time'             => $end->format('H:i:s'),
        ];

        $response = $this->post(route('admin.management.accounts.users.store'), $payload);

        $response->assertStatus(302);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', ['email' => 'testuser@example.com']);

        Notification::assertSentTo(
            $this->adminAccount,
            AdminNotification::class,
            function (AdminNotification $notification) {
                // This now works because we added toArray() to the class
                $data = $notification->toArray($this->adminAccount);

                return $data['title'] === 'User Created' &&
                    $data['nature'] === \App\Models\Activity::TYPE_INFO;
            }
        );
    }
    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_update_user
     * <Function> Test that an existing user can be updated in the database.
     *
     * @return void
     */
    public function test_update_user()
    {
        $room = Room::factory()->create();

        $user = User::factory()->create([
            'name' => 'Old Name',
            'username' => 'oldusername',
            'email' => 'olduser@example.com',
            'password' => Hash::make('123456'),
            'password_displayable' => '123456',
        ]);

        RoomAccess::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'start_date' => '2027-01-01 08:00:00',
            'end_date' => '2027-12-31 17:00:00',
        ]);

        $payload = [
            'name' => 'New Name',
            'username' => 'newusername',
            'email' => 'newuser@example.com',
            'password' => '654321',
            'password_displayable' => '654321',
            'is_permanent' => true,
            'room_id' => $room->id,
        ];

        $response = $this->from(route('admin.management.accounts.users.index'))
            ->put(
                route('admin.management.accounts.users.update', $user->id),
                $payload
            );

        $response->assertStatus(302);
        $user->refresh();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'username' => 'newusername',
            'email' => 'newuser@example.com',
        ]);

        $this->assertTrue(Hash::check('654321', $user->password));

        $this->assertDatabaseHas('room_accesses', [
            'user_id' => $user->id,
            'room_id' => $room->id,
            'start_date' => null,
            'end_date' => null,
        ]);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_destroy_user
     * <Function> Test that a user can be deleted from the database.
     *
     * @return void
     */
    public function test_destroy_user()
    {
        $user = User::factory()->create();

        $response = $this->delete(route('admin.management.accounts.users.destroy', $user->id));

        $response->assertStatus(302);

        // 1. Check that it is soft deleted (deleted_at is NOT NULL)
        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);

        // 2. Or, if you want to be very specific:
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'deleted_at' => now(), // Laravel handles the precision check here
        ]);
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> test_admin_can_export_users
     * <Function> Check if admin can export users to Excel
     *
     * @return void
     */
    public function test_admin_can_export_users()
    {
        $response = $this->get(route('admin.management.accounts.users.export'));

        $response->assertOk();
        $response->assertHeader('content-disposition');

        // Optional: check if filename includes "users_" + today's date
        $contentDisposition = $response->headers->get('content-disposition');
        $today = now('Asia/Manila')->format('Ymd');
        $this->assertStringContainsString("users_{$today}", $contentDisposition);
    }
}

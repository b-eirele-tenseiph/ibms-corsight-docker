<?php

namespace Tests\Feature\Notification;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    use RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // setUp                                                               (1.0)
    // test_admin_can_get_notifications_index                              (2.0)
    // test_user_can_get_notifications_index                               (3.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> setUp.
     * <Function> Migrate the database before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate');
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> test_admin_can_get_notifications_index
     * <Function> Test if the admin user can get notifications.
     *
     * @return void
     */
    public function test_admin_can_get_notifications_index(): void
    {
        $admin = Admin::factory()->create();
        $this->actingAs($admin, 'admin');

        $response = $this->getJson(route('notifications.index'));
        $response->assertOk();
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> test_user_can_get_notifications_index
     * <Function> Test if a regular user can get notifications.
     *
     * @return void
     */
    public function test_user_can_get_notifications_index()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'web');

        $response = $this->getJson(route('notifications.index'));
        $response->assertOk();
    }
}

<?php

namespace Tests\Feature\User\Dashboard;

use App\Models\Room;
use App\Models\RoomAccess;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // test_user_login_screen_can_be_rendered                              (1.0)
    // test_authenticated_user_can_access_dashboard                        (2.0)
    // test_unauthenticated_user_cannot_access_dashboard                   (3.0)
    // test_authenticated_user_can_hide_promo                              (4.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> test_user_login_screen_can_be_rendered
     * <Function> Ensure the user login page can be rendered successfully.
     *
     * @return void
     */
    public function test_user_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> test_authenticated_user_can_access_dashboard
     * <Function> Verify that an enabled and authenticated user can access the dashboard view.
     *
     * @return void
     */

    public function test_authenticated_user_can_access_dashboard()
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['number' => 314]);

        RoomAccess::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'is_permanent' => true,
        ]);

        $response = $this->actingAs($user, 'web')
            ->get("/user/{$room->number}/dashboard");

        $response->assertOk();
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> test_unauthenticated_user_cannot_access_dashboard
     * <Function> Ensure that unauthenticated user users are redirected to the login page.
     *
     * @return void
     */
    public function test_unauthenticated_user_cannot_access_dashboard()
    {
        $response = $this->get('/user/1/dashboard');

        $response->assertRedirect(route('login'));
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_authenticated_user_can_hide_promo
     * <Function> Ensure that authenticated user can hide promo popup
     *
     * @return void
     */
    public function test_authenticated_user_can_hide_promo()
    {
        $user = User::factory()->create();
        $room = Room::factory()->create(['number' => 314]);

        RoomAccess::create([
            'user_id' => $user->id,
            'room_id' => $room->id,
            'is_permanent' => true,
        ]);

        $response = $this->actingAs($user, 'web')
            ->get("/user/{$room->number}/dashboard");

        $response->assertOk();

        $payload = [
            'hide_promo_popup' => true,
        ];

        $response = $this->post(route('user.dashboard.hide-promo'), $payload);

        $response->assertStatus(200);

        $user->refresh();

        $this->assertTrue($user->hide_promo_popup);

        $this->assertDatabaseHas('users', [
            'hide_promo_popup' => true,
        ]);
    }
}

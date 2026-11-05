<?php

namespace Tests\Feature\User\Promo;

use App\Models\Promo;
use App\Models\Room;
use App\Models\RoomAccess;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PromoControllerTest extends TestCase
{
    use RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // setUp                                                               (1.0)
    // guest_is_redirected_to_login                                        (2.0)
    // index_renders_promos_with_images_for_authenticated_user             (3.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> setUp
     * <Function> Base bootstrap for feature tests. Uses RefreshDatabase to
     *            ensure a clean state for every test run.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> guest_is_redirected_to_login
     * <Function> Verify guests are redirected to the login page when accessing
     *            the promos index route.
     *
     * @return void
     */
    public function guest_is_redirected_to_login()
    {
        $this->get(route('user.promos.index'))
            ->assertRedirect(route('login'));
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> index_renders_promos_with_images_for_authenticated_user
     * <Function> AuthZ + listing +  image resolution.
     *
     * @return void
     */
    public function test_index_renders_promos_with_images_for_authenticated_user()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $user->assignRole('user');

        $room = Room::factory()->create(['number' => 314]);
        RoomAccess::create([
            'user_id'      => $user->id,
            'room_id'      => $room->id,
            'is_permanent' => true,
        ]);

        $this->actingAs($user, 'web');


        config([
            'filesystems.default' => 'public',
            'filesystems.disks.public.url' => '/storage',
        ]);


        $older = Promo::factory()
            ->relativeImage('promos/deluxe.jpg')
            ->create([
                'title'       => 'Old Deluxe',
                'description' => 'Old promo description',
                'created_at'  => now()->subDay(),
            ]);


        $newer = Promo::factory()
            ->absoluteImage('https://example.com/luxury.jpg')
            ->create([
                'title'       => 'New Luxury',
                'description' => 'New promo description',
                'created_at'  => now(),
            ]);


        $this->get(route('user.promos.index'))
            ->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('user/promo/Index')
                    ->has('promos', 2)


                    ->where('promos.0.id', $newer->id)
                    ->where('promos.0.photo_file_path', 'https://example.com/luxury.jpg')


                    ->where('promos.1.id', expected: $older->id)
                    ->where(
                        'promos.1.photo_file_path',
                        fn($value) => str_ends_with($value, 'promos/deluxe.jpg')
                    )
            );
    }
}

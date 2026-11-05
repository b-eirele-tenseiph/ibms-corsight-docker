<?php

namespace Tests\Feature\User\Amenity;

use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Amenity;
use App\Models\AmenityCategory;
use App\Models\AmenityPhoto;
use App\Models\Room;
use App\Models\RoomAccess;
use Tests\Traits\Permissions;
use Tests\Traits\Roles;

class AmenityControllerTest extends TestCase
{
    use RefreshDatabase, Permissions, Roles;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $user                                                               (1.0)
    // $room                                                               (2.0)
    // setUp                                                               (3.0)
    // test_it_shows_the_index_with_amenities_list_and_no_active_amenity   (4.0)
    // test_it_returns_active_amenity_when_show_query_is_present           (5.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $user
     * <Function> Holds the currently authenticated user instance.
     *
     * @return \App\Models\User
     */
    protected User $user;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $room
     * <Function> The room associated with the user.
     *
     * @return \App\Models\Room
     */
    protected Room $room;


    /**
     * <Layer number> (3.0)
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
        $this->createUserRole();

        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->user->assignRole('user');

        $this->room = Room::factory()->create(['number' => 314]);

        RoomAccess::create([
            'user_id'      => $this->user->id,
            'room_id'      => $this->room->id,
            'is_permanent' => true,
        ]);

        $this->actingAs($this->user, 'web');

        $this->get("/user/{$this->room->number}/dashboard")->assertOk();
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_it_shows_the_index_with_amenities_list_and_no_active_amenity
     * <Function> Check if the user can access the Amenities.
     *
     * @return void
     */
    public function test_it_shows_the_index_with_amenities_list_and_no_active_amenity()
    {
        Amenity::factory()
            ->count(3)
            ->for(AmenityCategory::factory(), 'amenityCategory')
            ->has(AmenityPhoto::factory()->count(2), 'photos')
            ->create();

        $response = $this->get(route('user.amenities.index'));

        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('user/amenities/Index')
                    ->has('amenities', 3)
                    ->where('activeAmenity', null)
            );
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_it_returns_active_amenity_when_show_query_is_present
     * <Function> Check if the user can access the Amenities.
     *
     * @return void
     */
    public function test_it_returns_active_amenity_when_show_query_is_present()
    {
        $target = Amenity::factory()
            ->for(AmenityCategory::factory(), 'amenityCategory')
            ->has(AmenityPhoto::factory()->count(3), 'photos')
            ->create();

        Amenity::factory()
            ->count(2)
            ->for(AmenityCategory::factory(), 'amenityCategory')
            ->has(AmenityPhoto::factory()->count(1), 'photos')
            ->create();
        $response = $this->get(route('user.amenities.index', ['show' => $target->id]));

        $response->assertOk()
            ->assertInertia(
                fn(Assert $page) => $page
                    ->component('user/amenities/Index')
                    ->has('amenities')
                    ->has(
                        'activeAmenity',
                        fn(Assert $amenity) => $amenity
                            ->where('id', $target->id)
                            ->has(
                                'amenity_category',
                                fn(Assert $cat) => $cat
                                    ->where('name', fn($v) => is_string($v))
                                    ->etc()
                            )
                            ->has(
                                'photos',
                                3,
                                fn(Assert $photo) => $photo
                                    ->whereType('id', 'string')
                                    ->where('file_path', fn($v) => is_string($v))
                                    ->etc()
                            )
                            ->etc()
                    )
            );
    }
}

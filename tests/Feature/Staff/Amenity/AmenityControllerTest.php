<?php

namespace Tests\Feature\Staff\Amenity;

use App\Http\Controllers\Staff\Amenity\AmenityController;
use App\Models\Amenity;
use App\Models\AmenityCategory;
use App\Models\AmenityPhoto;
use App\Models\User;
use App\Notifications\UserNotification;
use Illuminate\Support\Facades\Storage;
use Tests\Traits\{Data, Roles};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Illuminate\Support\Facades\Notification;

class AmenityControllerTest extends TestCase
{
    use Data, Roles, RefreshDatabase, WithFaker;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $adminAccount                                                       (1.0)
    // setUp                                                               (2.0)
    // createStaffUser                                                     (3.0)
    // accessStaffAmenities                                                (4.0)
    // test_index_displays_amenities_with_pagination                       (5.0)
    // test_store_creates_amenities                                        (6.0)
    // test_update_existing_amenity                                        (7.0)
    // test_destroy_amenity                                                (8.0)

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
     * <Processing name> setup
     * <Function> Prepares testing environment, including roles and permissions setup.
     *
     * @return void
     */
    protected function setup(): void
    {
        parent::setUp();

        $this->createPermissions();
        $this->createAdminRole();
        $this->createStaffRole();
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> createStaffUser
     * <Function> Creation of admin account with staff role
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable
     */
    private function createStaffUser()
    {
        $staff = $this->createAdminUser()->assignRole('staff');

        // Ensure permission exists
        Permission::findOrCreate('manage amenities', 'admin');

        // Assign permission to the user
        $staff->givePermissionTo('manage amenities');

        return $staff;
    }


    /**
     * <Layer number> (4.0)
     *
     * <Processing name> accessStaffAmenities
     * <Function> Simulates a staff user accessing the amenities index page.
     *
     * @return \Illuminate\Testing\TestResponse
     */
    private function accessStaffAmenities(): mixed
    {
        // Create staff user
        $staff = $this->createAdminUser()->assignRole('staff');

        // Ensure 'manage room access' permission exists
        Permission::findOrCreate('manage amenities', 'admin');

        // Assign permission directly to the user
        $staff->givePermissionTo('manage amenities');

        // Act as this staff and access the dashboard
        return $this->actingAs($staff, 'admin')
            ->get(action([AmenityController::class, 'index']))
            ->assertStatus(200)
            ->assertInertia(function (Assert $page) {
                $page->component('staff/amenity/Index');
            });
    }


    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_index_displays_amenities_with_pagination
     * <Function> Ensures the amenities index page displays paginated amenities.
     *
     * @return void
     */
    public function test_index_displays_amenities_with_pagination()
    {
        $staff = $this->createStaffUser();

        $response = $this->actingAs($staff, 'admin')
            ->get(route('staff.amenity_management.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('staff/amenity/Index')
                ->has('amenities.data')
        );
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_store_creates_amenities
     * <Function> Ensures staff can create a new amenity with photos and attributes.
     *
     * @return void
     */
    public function test_store_creates_amenities()
    {
        Notification::fake();
        Storage::fake('gcs');

        // Staff (admin guard) who can hit the route
        $staff = $this->createStaffUser();

        Permission::findOrCreate('access amenities', 'web');
        $recipients = User::factory()->count(2)->create();
        foreach ($recipients as $u) {
            $u->givePermissionTo('access amenities');
        }

        $category = AmenityCategory::factory()->create();
        $photo = UploadedFile::fake()->image('photo1.jpg');

        $formData = [
            'title' => 'Swimming Pool',
            'description' => 'Olympic-sized pool with heated water.',
            'category_id' => $category->id,
            'photo' => [$photo],
            'attributes' => [
                ['name' => 'Size',  'description' => '50m x 25m'],
                ['name' => 'Depth', 'description' => '2 meters'],
            ],
        ];

        $response = $this->actingAs($staff, 'admin')
            ->post(route('staff.amenity_management.store'), $formData);

        $response->assertRedirect();

        $this->assertDatabaseHas('amenities', [
            'title' => 'Swimming Pool',
            'description' => 'Olympic-sized pool with heated water.',
            'category_id' => $category->id,
        ]);

        $storedFile = AmenityPhoto::first()->file_path ?? null;
        $this->assertNotNull($storedFile);
        Storage::disk('gcs')->assertExists($storedFile);

        Notification::assertSentTo(
            $recipients,
            UserNotification::class,
            function ($notification, $channels) use ($recipients, $formData) {
                // Pull the payload the same way Laravel will store it
                $payload = $notification->toDatabase($recipients->first());

                return $payload['type']    === __('notification.amenity')
                    && $payload['title']   === __('notification.amenities.title_add')
                    && $payload['content'] === $formData['description']
                    && $payload['url']     === route('user.amenities.index');
            }
        );
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_update_existing_amenity
     * <Function> Ensures staff can update an amenity, replace photos, and update attributes.
     *
     * @return void
     */
    public function test_update_existing_amenity()
    {
        Notification::fake();
        $staff = $this->createStaffUser();

        Permission::findOrCreate('access amenities', 'web');
        $recipients = User::factory()->count(2)->create();
        foreach ($recipients as $u) {
            $u->givePermissionTo('access amenities');
        }

        Storage::fake('gcs');

        $categoryOld = AmenityCategory::factory()->create();
        $categoryNew = AmenityCategory::factory()->create();

        $amenity = Amenity::create([
            'title' => 'Old Title',
            'description' => 'Old description',
            'category_id' => $categoryOld->id,
            'attributes' => [
                ['name' => 'Attribute 1', 'description' => 'Value 1']
            ]
        ]);

        // Create existing photo manually
        $oldPhoto = AmenityPhoto::create([
            'amenity_id' => $amenity->id,
            'file_path' => 'amenities/oldphoto.jpg'
        ]);

        // Prepare new uploaded file
        $newPhoto = UploadedFile::fake()->image('newphoto.jpg');

        // Prepare update data
        $updateData = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'category_id' => $categoryNew->id,
            'photo' => [$newPhoto],
            'existing_photo_ids' => [$oldPhoto->id],
            'attributes' => [
                ['name' => 'Updated Attribute', 'description' => 'Updated Value']
            ]
        ];

        // Send request
        $response = $this->actingAs($staff, 'admin')
            ->post(route('staff.amenity_management.update', $amenity), $updateData);

        // Assert redirect
        $response->assertRedirect();

        // Assert amenity is updated
        $this->assertDatabaseHas('amenities', [
            'id' => $amenity->id,
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'category_id' => $categoryNew->id,
        ]);

        // Assert new photo exists in storage
        $storedFile = AmenityPhoto::where('amenity_id', $amenity->id)
            ->where('id', '!=', $oldPhoto->id)
            ->first()
            ->file_path ?? null;

        Storage::disk('gcs')->assertExists($storedFile);

        Notification::assertSentTo(
            $recipients,
            UserNotification::class,
            function ($notification, $channels) use ($recipients, $updateData) {
                $payload = $notification->toDatabase($recipients->first());

                return $payload['type']    === __('notification.amenity')
                    && $payload['title']   === __('notification.amenities.title_update')
                    && $payload['content'] === $updateData['description']
                    && $payload['url']     === route('user.amenities.index');
            }
        );
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> test_destroy_amenity
     * <Function> Ensures staff can delete an amenity and verify it no longer exists in DB.
     *
     * @return void
     */
    public function test_destroy_amenity()
    {
        // Create staff with permission
        $staff = $this->createStaffUser();

        $category = AmenityCategory::create([
            'name' => 'Test Category',
        ]);

        $amenity = Amenity::create([
            'title' => 'Sample Amenity',
            'description' => 'Description',
            'category_id' => $category->id,
            'attributes' => [
                ['name' => 'phone', 'description' => '12345678']
            ]
        ]);

        $response = $this->actingAs($staff, 'admin')
            ->delete(route(
                'staff.amenity_management.destroy',
                $amenity->id
            ));

        $response->assertStatus(302);
        $this->assertDatabaseMissing(
            'amenities',
            [
                'id' => $amenity->id
            ]
        );
    }
}

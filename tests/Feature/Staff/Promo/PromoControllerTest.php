<?php

namespace Tests\Feature\Staff\Promo;

use App\Http\Controllers\Staff\Promo\PromoController;
use App\Models\Promo;
use App\Services\Promo\PromoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Traits\{Data, Roles};
use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;

class PromoControllerTest extends TestCase
{
    use Data, Roles, RefreshDatabase, WithFaker;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $adminAccount                                                      (1.0)
    // setUp                                                              (2.0)
    // createStaffUser                                                    (3.0)
    // accessStaffPromos                                                  (4.0)
    // test_index_displays_promos_with_pagination                         (5.0)
    // test_store_creates_promos                                          (6.0)
    // mockSaveAccessMethod                                               (7.0)
    // test_update_edits_existing_promo_and_replaces_photo                (8.0)
    // mockUpdatePromo                                                    (9.0)
    // test_destroy_deletes_promo_and_photo_from_storage                  (10.0)
    // mockDeletePromo                                                    (11.0)

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
        Permission::findOrCreate('manage promos', 'admin');

        // Assign permission to the user
        $staff->givePermissionTo('manage promos');

        return $staff;
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> accessStaffPromos
     * <Function> Simulates a staff user accessing the promos index page.
     *
     * @return \Illuminate\Testing\TestResponse
     */
    private function accessStaffPromos()
    {
        // Create staff user
        $staff = $this->createAdminUser()->assignRole('staff');

        // Ensure 'manage room access' permission exists
        Permission::findOrCreate('manage amenities', 'admin');

        // Assign permission directly to the user
        $staff->givePermissionTo('manage amenities');

        // Act as this staff and access the dashboard
        return $this->actingAs($staff, 'admin')
            ->get(action([PromoController::class, 'index']))
            ->assertStatus(200)
            ->assertInertia(function (Assert $page) {
                $page->component('staff/promo/Index');
            });
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_index_displays_promos_with_pagination
     * <Function> Ensures the promos index page displays paginated promos.
     *
     * @return void
     */
    public function test_index_displays_promos_with_pagination()
    {
        $staff = $this->createStaffUser();

        $response = $this->actingAs($staff, 'admin')
            ->get(route('staff.promo_management.index'));

        $response->assertStatus(200);
        $response->assertInertia(
            fn(Assert $page) =>
            $page->component('staff/promo/Index')
                ->has('promos.data')
        );
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_store_creates_promos
     * <Function> Ensures staff can create a new promos with photo and attributes.
     *
     * @return void
     */
    public function test_store_creates_promos()
    {
        // Fake Google Cloud Storage for testing
        Storage::fake('gcs');

        // Create a staff user for authentication
        $staff = $this->createStaffUser();

        // Fake photo upload
        $photo = UploadedFile::fake()->image('photo1.jpg');

        // Form data for creating the promo
        $formData = [
            'title' => 'Swimming Pool',
            'description' => 'Olympic-sized pool with heated water.',
            'valid_start_date' => now()->toDateString(),
            'valid_end_date' => now()->addDays(5)->toDateString(),
            'photo_file' => $photo,
        ];

        // Mock the PromoService's createPromo method
        $this->mockSaveAccessMethod($formData);

        // Act as the staff user and send a POST request to store the promo
        $response = $this->actingAs($staff, 'admin')
            ->post(route('staff.promo_management.store'), $formData);

        // Assert the response is a redirect (indicating success)
        $response->assertRedirect();

        // Assert that the promo is in the database
        $this->assertDatabaseHas('promos', [
            'title' => 'Swimming Pool',
            'description' => 'Olympic-sized pool with heated water.',
        ]);

        // Get the first promo record
        $promo = Promo::first();

        // Assert that the photo file path is not null
        $this->assertNotNull($promo->photo_file_path);

        // Assert that the file exists in the 'gcs' disk
        Storage::disk('gcs')->assertExists($promo->photo_file_path);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> mockSaveAccessMethod
     * <Function> Mock the PromoService's updatePromo method.
     *
     * @param Promo $promo
     * @param array $formData
     */
    private function mockSaveAccessMethod(array $formData): void
    {
        // Create a mock Promo object that mimics the behavior of a real Promo
        $promoMock = new Promo([
            'title' => 'Swimming Pool',
            'description' => 'Olympic-sized pool with heated water.',
            'valid_start_date' => now()->toDateString(),
            'valid_end_date' => now()->addDays(5)->toDateString(),
        ]);

        // Mock the PromoService to return the mocked Promo instance when createPromo is called
        $this->app->instance(
            PromoService::class,
            $this->partialMock(PromoService::class, function ($mock) use ($formData, $promoMock) {
                $mock->shouldReceive('createPromo')
                    ->with($formData)
                    ->andReturn($promoMock);
            })
        );
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> test_update_edits_existing_promo_and_replaces_photo
     * <Function> Ensures staff can edit an existing promo, update its details, and replace the photo.
     *
     * @return void
     */
    public function test_update_edits_existing_promo_and_replaces_photo()
    {
        Storage::fake('gcs'); // Fake the GCS storage
        $staff = $this->createStaffUser(); // Create a staff user for authentication

        // Create initial promo
        $promo = Promo::factory()->create([
            'title' => 'Old Title',
            'description' => 'Old description',
            'valid_start_date' => now()->toDateString(),
            'valid_end_date' => now()->addDays(3)->toDateString(),
            'photo_file_path' => 'old_photo.jpg',
        ]);

        // New data to update the promo
        $newPhoto = UploadedFile::fake()->image('new_photo.jpg');
        $formData = [
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'valid_start_date' => now()->toDateString(),
            'valid_end_date' => now()->addDays(7)->toDateString(),
            'photo_file' => $newPhoto,
        ];

        // Mock the PromoService update method
        $this->mockUpdatePromo($promo, $formData, $newPhoto);

        // Act as the staff user and send a POST request to update the promo
        $response = $this->actingAs($staff, 'admin')
            ->post(route('staff.promo_management.update', $promo), $formData);

        // Assert the response is a redirect (indicating success)
        $response->assertRedirect();

        // Assert the promo is updated in the database
        $this->assertDatabaseHas('promos', [
            'id' => $promo->id,
            'title' => 'Updated Title',
            'description' => 'Updated description',
        ]);

        // Refresh the promo to check the updated values
        $promo->refresh();

        // Assert the photo path has been updated (not equal to the old photo)
        $this->assertNotEquals('old_photo.jpg', $promo->photo_file_path);

        // Assert that the new photo file exists in GCS storage
        Storage::disk('gcs')->assertExists($promo->photo_file_path);
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> mockUpdatePromo
     * <Function> Mock the PromoService's updatePromo method.
     *
     * @param Promo $promo
     * @param array $formData
     * @param UploadedFile $newPhoto
     */
    private function mockUpdatePromo(Promo $promo, array $formData, UploadedFile $newPhoto): void
    {
        // Create a mock promo that simulates the updated promo
        $updatedPromo = new Promo([
            'id' => $promo->id,
            'title' => 'Updated Title',
            'description' => 'Updated description',
            'valid_start_date' => now()->toDateString(),
            'valid_end_date' => now()->addDays(7)->toDateString(),
            'photo_file_path' => 'new_photo.jpg', // The new photo path
        ]);

        // Mock the PromoService updatePromo method to return the updated promo
        $this->app->instance(
            PromoService::class,
            $this->partialMock(PromoService::class, function ($mock) use ($promo, $formData, $updatedPromo) {
                $mock->shouldReceive('updatePromo')
                    ->with($promo, $formData)
                    ->andReturn($updatedPromo);
            })
        );
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> test_destroy_deletes_promo_and_photo_from_storage
     * <Function> Ensures staff can delete a promo and its associated photo from storage.
     *
     * @return void
     */
    public function test_destroy_deletes_promo_and_photo_from_storage()
    {
        Storage::fake('gcs'); // Fake the GCS storage
        $staff = $this->createStaffUser(); // Create a staff user for authentication

        // Create a promo with a photo
        $promo = Promo::factory()->create([
            'title' => 'Promo to Delete',
            'description' => 'Should be removed',
            'valid_start_date' => now()->toDateString(),
            'valid_end_date' => now()->addDays(2)->toDateString(),
            'photo_file_path' => 'delete_me.jpg',
        ]);

        // Put a fake file into the GCS storage
        Storage::disk('gcs')->put('delete_me.jpg', 'fake content');

        // Mock the PromoService's deletePromo method to simulate the deletion
        $this->mockDeletePromo($promo);

        // Act as the staff user and send a DELETE request to delete the promo
        $response = $this->actingAs($staff, 'admin')
            ->delete(route('staff.promo_management.destroy', $promo));

        // Assert the response is a redirect (indicating success)
        $response->assertRedirect();

        // Assert the promo is removed from the database
        $this->assertDatabaseMissing('promos', ['id' => $promo->id]);

        // Assert the photo file is deleted from GCS storage
        Storage::disk('gcs')->assertMissing('delete_me.jpg');
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> mockDeletePromo
     * <Function> Mock the PromoService's deletePromo method.
     *
     * @param Promo $promo
     */
    private function mockDeletePromo(Promo $promo): void
    {
        // Mock the PromoService to simulate deleting the promo and its photo
        $this->app->instance(
            PromoService::class,
            $this->partialMock(PromoService::class, function ($mock) use ($promo) {
                // Mock the deletePromo method to do nothing
                $mock->shouldReceive('deletePromo')
                    ->with($promo)
                    ->andReturn(true); // Simulate successful deletion
            })
        );
    }
}

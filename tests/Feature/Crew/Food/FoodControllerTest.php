<?php

namespace Tests\Feature\Crew\Food;

use App\Models\Admin;
use App\Models\Food;
use App\Models\FoodCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;

class FoodControllerTest extends TestCase
{
    use RefreshDatabase;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $perm                                                               (1.0)
    // setUp                                                               (2.0)
    // test_displays_the_food_index_page                                   (3.0)
    // test_can_store_a_new_food                                           (4.0)
    // test_can_update_an_existing_food                                    (5.0)
    // test_can_delete_a_food                                              (6.0)
    // test_can_update_availability_via_json                               (7.0)

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $perm
     * <Function> The permission string required to manage foods.
     *
     * @var string
     */
    private string $perm = 'manage orders';


    /**
     * <Layer number> (2.0)
     *
     * <Processing name> setUp.
     * <Function> Method that runs before each test to set up the environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // ensure Spatie permission cache is clear and the permission exists on the admin guard
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        Permission::findOrCreate($this->perm, 'admin');
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> test_displays_the_food_index_page.
     * <Function> Displays the index page and verifies food name.
     *
     * @return void
     */
    public function test_displays_the_food_index_page(): void
    {
        $admin = Admin::factory()->create(['enabled' => true]);
        $admin->givePermissionTo($this->perm);
        $this->actingAs($admin, 'admin');

        $cat  = FoodCategory::factory()->create();
        $food = Food::factory()->create([
            'category_id' => $cat->id,
        ]);

        $response = $this->get(route('crew.foods.index'));

        $response->assertStatus(200);
        $response->assertSee($food->name);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> test_can_store_a_new_food.
     * <Function> Stores a new food and verifies database state.
     *
     * @return void
     */
    public function test_can_store_a_new_food(): void
    {
        Storage::fake('gcs');

        $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        $admin = Admin::factory()->create(['enabled' => true]);
        $admin->givePermissionTo($this->perm);
        $this->actingAs($admin, 'admin');

        $cat = FoodCategory::factory()->create();

        $payload = [
            'name'        => 'Chicken Adobo',
            'category_id' => $cat->id,
            'description' => 'Classic savory-sour stew.',
            'price'       => 199.5,
            'available'   => true,
            'image'       => $file,
        ];

        $response = $this->post(route('crew.foods.store'), $payload);

        $response->assertRedirect(route('crew.foods.index'));
        $this->assertDatabaseHas('foods', [
            'name'        => 'Chicken Adobo',
            'category_id' => $cat->id,
            'available'   => 1,
        ]);
        // price is cast to decimal(2), so it persists as a string "199.50"
        $this->assertDatabaseHas('foods', ['price' => '199.50']);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> test_can_update_an_existing_food.
     * <Function> Updates a food and verifies changes.
     *
     * @return void
     */
    public function test_can_update_an_existing_food(): void
    {
        Storage::fake('gcs');

        $file = UploadedFile::fake()->image('avatar.jpg', 100, 100);

        $admin = Admin::factory()->create(['enabled' => true]);
        $admin->givePermissionTo($this->perm);
        $this->actingAs($admin, 'admin');

        $cat  = FoodCategory::factory()->create();
        $food = Food::factory()->create([
            'category_id' => $cat->id,
            'available'   => true,
        ]);

        $newCat = FoodCategory::factory()->create();

        $response = $this->from(route('crew.foods.index'))->post(route('crew.foods.update', $food), [
            'name'        => 'Updated Adobo',
            'category_id' => $newCat->id,
            'description' => 'Updated description',
            'price'       => 250.0,
            'available'   => false,
            'image'       => $file,
        ]);

        $response->assertRedirect(route('crew.foods.index'));

        $this->assertDatabaseHas('foods', [
            'id'          => $food->id,
            'name'        => 'Updated Adobo',
            'category_id' => $newCat->id,
            'available'   => 0,
        ]);
        $this->assertDatabaseHas('foods', ['id' => $food->id, 'price' => '250.00']);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> test_can_delete_a_food.
     * <Function> Deletes a food and checks database state.
     *
     * @return void
     */
    public function test_can_delete_a_food(): void
    {
        Storage::fake('gcs');

        $admin = Admin::factory()->create(['enabled' => true]);
        $admin->givePermissionTo($this->perm);
        $this->actingAs($admin, 'admin');

        $cat  = FoodCategory::factory()->create();
        $food = Food::factory()->create(['category_id' => $cat->id]);

        $this->assertDatabaseHas('foods', ['id' => $food->id]);

        $response = $this->delete(route('crew.foods.delete', $food));

        // Controller redirects back() with a flash — just assert it redirected
        $response->assertRedirect();

        $this->assertDatabaseMissing('foods', ['id' => $food->id]);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> test_can_update_availability_via_json.
     * <Function> Updates availability via JSON (expects 204, no redirect).
     *
     * @return void
     */
    public function test_can_update_availability_via_json(): void
    {
        $admin = Admin::factory()->create(['enabled' => true]);
        $admin->givePermissionTo($this->perm);
        $this->actingAs($admin, 'admin');

        $cat  = FoodCategory::factory()->create();
        $food = Food::factory()->create([
            'category_id' => $cat->id,
            'available'   => true,
        ]);

        // Put JSON so $request->expectsJson() === true in controller
        $response = $this->patch(route('crew.foods.patch', $food), [
            'available' => false,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('foods', [
            'id'        => $food->id,
            'available' => 0,
        ]);
    }
}

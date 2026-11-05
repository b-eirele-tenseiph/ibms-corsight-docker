<?php

namespace Database\Factories;

use App\Models\AmenityCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Amenity>
 */
class AmenityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->streetName,
            'description' => $this->faker->sentences(3, true),
            'category_id' => AmenityCategory::factory(),
            'attributes' => $this->faker->randomElements([
                'attribute1' => $this->faker->word,
                'attribute2' => $this->faker->word,
                'attribute3' => $this->faker->word,
            ], 3),
        ];
    }
}

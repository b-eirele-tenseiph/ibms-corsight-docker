<?php

namespace Database\Factories;

use App\Models\Food;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FoodFactory extends Factory
{
    protected $model = Food::class;

    /**
     *
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => Str::uuid(),
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'category_id' => \App\Models\FoodCategory::factory(),
            'price' => $this->faker->randomFloat(2, 1, 100),
            'image' => $this->faker->imageUrl(),
            'available'   => true,
        ];
    }
}

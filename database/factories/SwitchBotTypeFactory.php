<?php

namespace Database\Factories;

use App\Models\SwitchBotType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SwitchBotType>
 */
class SwitchBotTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => $this->faker->name(),
            'description' => $this->faker->sentence(),
            'category'    => $this->faker->boolean()
                ? SwitchBotType::CATEGORY_DEVICE
                : SwitchBotType::CATEGORY_REMOTE
        ];
    }
}

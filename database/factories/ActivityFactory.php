<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Activity>
 */
class ActivityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'log_name' => $this->faker->randomElement(['error', 'information', 'warning']),
            'description' => $this->faker->sentence,
            'event' => $this->faker->word,
            'subject_id' => $this->faker->randomNumber(),
            'subject_type' => $this->faker->word,
            'causer_id' => $this->faker->randomNumber(),
            'causer_type' => $this->faker->randomElement(
                [
                    \App\Models\Admin::class,
                    \App\Models\User::class,
                    'System'
                ]
            ),
            'properties' => [
                'code'  => $this->faker->randomElement(['I000', 'E002', 'W003']),
                'room'  => $this->faker->randomElement(['Room 1', 'Room 2', 'Room 3']),
                'floor' => $this->faker->randomElement(['LG', 'UG', 'L2']),
                'data'  => $this->faker->word
            ],
            'batch_uuid' => $this->faker->optional()->uuid,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

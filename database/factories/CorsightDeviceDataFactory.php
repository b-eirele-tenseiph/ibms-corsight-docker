<?php

namespace Database\Factories;

use App\Models\CorsightDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CorsightDeviceData>
 */
class CorsightDeviceDataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'event_id'  => $this->faker->unique()->uuid,
            'camera_id' => $this->faker->uuid,
            'device_id' => CorsightDevice::factory(),
            'event_type' => $this->faker->randomElement(['appearance', 'entry', 'exit', 'new_poi']),
            'context'    => [
                'event_id'   => $this->faker->uuid,
                'event_type' => 'appearance',
            ],
            'received_at' => $this->faker->dateTimeBetween('-1 day', 'now'),
        ];
    }
}

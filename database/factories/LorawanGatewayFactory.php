<?php

namespace Database\Factories;

use App\Models\Floor;
use Illuminate\Database\Eloquent\Factories\Factory;

class LorawanGatewayFactory extends Factory
{

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'floor_id' => Floor::factory(),
            'ip_address' => $this->faker->ipv4,
            'mac_address' => $this->faker->macAddress,
            'connection' => $this->faker->randomElement(['thingpark', 'milesight', 'kerlink']),
        ];
    }
}

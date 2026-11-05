<?php

namespace Database\Factories;

use App\Models\LorawanDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

class LorawanDeviceDataFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'device_id' => $this->device?->id ?? LorawanDevice::factory(),
            'transmission' => $this->faker->randomElement(['uplink', 'downlink']),
            'type' => $this->faker->randomElement(['switch_1', 'switch_2', 'switch_3']),
            'context' => $this->switchContext(),
            'received_at' => now(),
        ];
    }


    /**
     * Set an array of context values for switch.
     *
     * @return array
     */
    public function switchContext()
    {
        return [
            'value' => $this->faker->randomElement(['low', 'high']),
            'unit' =>  null,
        ];
    }

    public function forDevice(LorawanDevice $device)
    {
        return $this->state([
            'device_id' => $device->id,
        ]);
    }
}

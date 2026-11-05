<?php

namespace Database\Factories;

use App\Models\LorawanDevice;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Scene>
 */
class SceneFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $room = Room::factory()->create();
        $device = LorawanDevice::factory()->create();
        return [
            'id' => $this->faker->uuid(),
            'name' => $this->faker->name(),
            'room_id' => $room->id,
            'context' => [
                [
                    'color' => $this->faker->colorName(),
                    'icon' => $this->faker->name(),
                ]
            ],
            'scheduled' => false,
            'time' => $this->faker->time(),
            'days' => $this->faker->optional()->randomElement([json_encode(['Monday', 'Wedsnesday', 'Friday']), null]),
        ];
    }
}

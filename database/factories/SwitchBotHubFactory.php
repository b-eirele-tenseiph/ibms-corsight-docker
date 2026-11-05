<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Room;
use App\Models\RoomArea;
use App\Models\SwitchBotHub;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SwitchBotHub>
 */
class SwitchBotHubFactory extends Factory
{
    protected $model = SwitchBotHub::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Str::uuid(),
            'name' => $this->faker->word . ' Hub',
            'serial_number' => $this->faker->unique()->macAddress(),
            'type_id' => $this->faker->randomElement(['Hub', 'Hub Mini', 'Hub Plus']),
            'room_id' => Room::factory()->create()->id,
            'room_area_id' => RoomArea::factory()->create()->id,
        ];
    }
}

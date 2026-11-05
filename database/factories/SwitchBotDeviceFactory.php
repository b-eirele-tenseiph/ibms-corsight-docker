<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomArea;
use App\Models\SwitchBotAccount;
use App\Models\SwitchBotType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SwitchBotDevice>
 */
class SwitchBotDeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $room = Room::factory()->create();
        $roomArea = RoomArea::factory()->create();
        $type = SwitchBotType::factory()->create();
        SwitchBotAccount::factory()->state(["room_id" => $room->id])->create();

        return [
            'id' => $this->faker->uuid,
            'name' => $this->faker->name(),
            'serial_number' => $this->faker->unique()->regexify('\d{2}-\d{8}(\d{6}-\d{8})?'),
            'type_id' => $type->id,
            'room_id' => $room->id,
            'room_area_id' => $roomArea->id,
            'enable_data_storage' => true,
        ];
    }
}

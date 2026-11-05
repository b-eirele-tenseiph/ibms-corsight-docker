<?php

namespace Database\Factories;

use App\Models\SwitchBotHub;
use App\Models\SwitchBotRemote;
use App\Models\SwitchBotRemoteData;
use App\Models\SwitchBotType;
use Illuminate\Database\Eloquent\Factories\Factory;

class SwitchBotRemoteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        foreach (SwitchBotRemote::TYPES as $type) {
            SwitchBotType::updateOrCreate([
                'category' => SwitchBotType::CATEGORY_REMOTE,
                'name' => $type
            ]);
        }

        $type = SwitchBotType::where('category', SwitchBotType::CATEGORY_REMOTE)->first();

        return [
            'name' => $this->faker->unique()->word(),
            'serial_number' => $this->faker->unique()->regexify('\d{2}-\d{8}(\d{6}-\d{8})?'),
            'type_id' => $type->id,
            'hub_id' => SwitchBotHub::factory()->createOne()
        ];
    }
}

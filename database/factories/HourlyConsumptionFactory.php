<?php

namespace Database\Factories;

use App\Models\HourlyConsumption;
use App\Models\LorawanDevice;
use Illuminate\Database\Eloquent\Factories\Factory;

class HourlyConsumptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = HourlyConsumption::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'date' => $this->faker->dateTimeThisMonth(),
            'device_id' => LorawanDevice::factory(),
            'floor_name' => $this->faker->word(),
            'consumption' => [
                'type'  => 'positive_active_energy',
                'value' => $this->faker->randomFloat(0, 1, 2),
                'current' => $this->faker->randomFloat(2, 200, 300)
            ]

        ];
    }
}

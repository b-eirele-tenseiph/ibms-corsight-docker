<?php

namespace Database\Factories;

use App\Models\MonthlyConsumption;
use Illuminate\Database\Eloquent\Factories\Factory;

class MonthlyConsumptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MonthlyConsumption::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        // Generate random consumption value (you can adjust the range as needed)
        $consumption = rand(500, 5000);

        // Generate a random date within the past 3 months
        $date = $this->faker->dateTimeBetween('-3 months', 'now')->format('Y-m-d');

        return [
            'floor_name' => '7th',
            'date' => $date,
            'consumption' =>  $consumption,
        ];
    }
}

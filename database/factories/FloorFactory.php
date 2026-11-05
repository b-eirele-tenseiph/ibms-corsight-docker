<?php

namespace Database\Factories;

use App\Models\Floor;
use Illuminate\Database\Eloquent\Factories\Factory;



class FloorFactory extends Factory
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // (1.0) $model         The associated model for this factory.
    // (2.0) definition()   Generate fake data for the Floor model.

    /**
     * (1.0)
     *
     * Processing: $model
     * Function: The associated model for this factory.
     *
     * @var string
     */
    protected $model = Floor::class;

    /**
     * (2.0)
     *
     * Processing: definition()
     * Function: Generate fake data for the Floor model.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->unique()->word() . ' Floor',
        ];
    }
}

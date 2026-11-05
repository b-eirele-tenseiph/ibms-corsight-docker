<?php

namespace Database\Factories;

use App\Models\Room;
use App\Models\RoomArea;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomAreaFactory extends Factory
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // (1.0) $model         The associated model for this factory.
    // (2.0) definition()   Generate fake data for the Room Area model.

    /**
     * (1.0)
     *
     * Processing: $model
     * Function: The associated model for this factory.
     *
     * @var string
     */
    protected $model = RoomArea::class;

    /**
     * (2.0)
     *
     * Processing: definition()
     * Function: Generate fake data for the Room Area model.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'name' => $this->faker->unique()->word(),
        ];
    }
}

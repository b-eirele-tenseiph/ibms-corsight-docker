<?php

namespace Database\Factories;

use App\Models\Floor;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // (1.0) definition()                 Generate fake data for the Room model.
    // (2.0) configure()                  Configure factory behavior after model
    //                                    creation.
    //

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> definition
     * <Function> Generate fake data for the Room model.
     *
     * @return array
     */
    public function definition()
    {

        return [
            'number' => (string) $this->faker->unique()->numberBetween(1000, 9999),
            'name' => $this->faker->unique()->word(),
            'floor_id' => Floor::factory()->create(),
            'external_apps' => $this->faker->randomElements(['LG', 'ELECTROLUX', 'AXIS', 'TUYA'], $this->faker->numberBetween(1, 2)),

        ];
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> configure
     * <Function> Configure factory behavior after model creation.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterCreating(function (Room $room) {
            $room->roomAreas()->create([
                'name' => $this->faker->word()
            ]);
        });
    }
}

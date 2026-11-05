<?php

namespace Database\Factories;

use App\Models\LorawanGateway;
use App\Models\Manufacturer;
use App\Models\Room;
use App\Models\RoomArea;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LorawanDeviceFactory extends Factory
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // definition             (1.0) create an array for sample data creation.
    // forExport              (2.0) create an array for exporting template data.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> definition
     * <Function> create an array for 
     *
     * @return array
     */
    public function definition()
    {
        // Ensure manufacturer exists
        $manufacturer = Manufacturer::firstOrCreate([
            'name' => Manufacturer::MFR_MILESIGHT,
        ], [
            'prefix_regex' => "/^(24e124[0-9a-f]{10})$/i",
        ]);

        return [
            'dev_eui' => strtoupper(bin2hex(random_bytes(8))),
            'name' => $this->faker->unique()->bothify('### Light'),
            'gateway_id' => LorawanGateway::factory(),
            'manufacturer_id' => $manufacturer->id,
            'model' => $this->faker->randomElement(['WS501', 'WS502', 'WS503']),
            'context' => null,
            'room_id' => Room::factory(),
            'room_area_id' => RoomArea::factory(),
            'enable_data_storage' => true,
        ];
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> forExport
     * <Function> create an array for exporting template data.
     *
     * @return array
     */
    public function forExport(): static
    {
        return $this->state(function () {
            return [
                'dev_eui'         => strtoupper(bin2hex(random_bytes(8))),
                'name'            => 'Light ' . Str::upper($this->faker->bothify('###-???')),
                'gateway_ip'      => $this->faker->ipv4,
                'manufacturer_id' => $this->faker->numberBetween(1, 9_999_999),
                'room_id'         => Room::factory()->make(),
                'room_area_id'    => RoomArea::factory()->make(),
                'model'           => $this->faker->randomElement(['WS501', 'WS502', 'WS503']),
                'context'         => null,
                'online_flag'     => $this->faker->boolean(90),
            ];
        });
    }
}

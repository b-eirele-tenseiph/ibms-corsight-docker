<?php

namespace App\Decoders\Acrel;

use App\Contracts\Acrel\DecoderContract as AcrelDecoder;

class ADW300 extends AcrelDecoder
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // prefixes          (1.0) The prefixes with their corresponding value.
    // dataTypes         (2.0) The data types that will be saved to the database.
    //                          If returns an empty array, save all data types.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> prefixes
     * <Function> The prefixes with their corresponding value.
     *
     * @return array
     */
    protected function prefixes()
    {
        return [
            'voltage_a' => 1,
            'voltage_b' => 2,
            'voltage_c' => 3,
            'voltage_ab' => 4,
            'voltage_bc' => 5,
            'voltage_ca' => 6,

            'electricity_a' => 7,
            'electricity_b' => 8,
            'electricity_c' => 9,

            'active_power_a' => 10,
            'active_power_b' => 11,
            'active_power_c' => 12,
            'total_active_power' => 13,

            'power_factor_a' => 22,
            'power_factor_b' => 23,
            'power_factor_c' => 24,
            'power_factor_total' => 25,

            'frequency' => 26,

            //consumption
            'positive_active_energy' => 27,
            'active_energy_a' => 33,
            'active_energy_b' => 37,
            'active_energy_c' => 41,
        ];
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> dataTypes
     * <Function> The data types that will be saved to the database.
     *            If returns an empty array, save all data types.
     *
     * @return string[]
     */
    protected function dataTypes(): array
    {
        return [
            'voltage_a',
            'voltage_b',
            'voltage_c',
            'voltage_ab',
            'voltage_bc',
            'voltage_ca',

            'electricity_a',
            'electricity_b',
            'electricity_c',

            'active_power_a',
            'active_power_b',
            'active_power_c',
            'total_active_power',

            'power_factor_a',
            'power_factor_b',
            'power_factor_c',
            'power_factor_total',

            'frequency',

            'positive_active_energy',
            'active_energy_a',
            'active_energy_b',
            'active_energy_c',
        ];
    }
}

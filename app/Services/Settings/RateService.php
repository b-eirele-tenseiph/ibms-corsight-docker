<?php

namespace App\Services\Settings;

use App\Models\ElectricityRate;
use App\Models\WaterRate;

class RateService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getGeneralData     (1.0) Retrieves a general settings data.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getGeneralData
     * <Function> Retrieves a general settings data.
     *
     * @return array
     */
    public function getRateData(): array
    {
        return [
            'consumer_types'          => array_map(fn($name) => ['name' => $name],   WaterRate::consumer_type),
            'electricity_rates'       => ElectricityRate::orderByDesc('effective_date')->get(['id', 'effective_date', 'rate_per_kwh']),
            'electricity_latest_rate' => ElectricityRate::query()->latestRate()->firstOr(fn() => ['effective_date' => null, 'rate_per_kwh' => 0]),
            'water_latest_rates'      => WaterRate::latestRateWithHistory()->get(['id', 'consumer_type', 'meter_size', 'notes'])

        ];
    }
}

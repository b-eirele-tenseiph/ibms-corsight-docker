<?php

namespace App\Services\Report;

use App\Models\WaterRate;

class WaterBillingReportService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // waterRateData     (1.0) Retrieve the latest water rate data for a given
    //                         consumer type.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> waterRateData
     * <Function> Retrieve the latest water rate data for a given consumer type.
     *
     * @param string $class
     * @return array
     */
    public function waterRateData(string $class): array
    {
        $rates = WaterRate::select('id', 'meter_size', 'tiers', 'effective_date')
            ->where('consumer_type', $class)
            ->where('effective_date', WaterRate::where('consumer_type', $class)->max('effective_date'))
            ->get();

        return $rates->isEmpty() ? [] : [[
            'id' => $rates->first()->id,
            'consumer_type' => $class,
            'effective_date' => $rates->first()->effective_date,
            'rates' => $rates->toArray()
        ]];
    }
}

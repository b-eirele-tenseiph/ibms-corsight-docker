<?php

namespace App\Observers\ElectricMeter;

use App\Events\Lorawan\ElectricMeter\HourlyConsumptionData;
use App\Models\HourlyConsumption;
use Carbon\Carbon;

class HourlyConsumptionDataObserver
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // created         (1.0) Handle the HourlyConsumption "created" event.
    // updated         (2.0) Handle the HourlyConsumption "updated" event.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> created
     * <Function> Handle the HourlyConsumption "created" event.
     *
     * @param  \App\Models\HourlyConsumption  $hourlyConsumption
     * @return void
     */
    public function created(HourlyConsumption $hourlyConsumption): void
    {
        $date = Carbon::parse($hourlyConsumption->date);

        if ($date->lte(now())) {
            HourlyConsumptionData::dispatch($hourlyConsumption);
        }
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> updated
     * <Function> Handle the HourlyConsumption "updated" event.
     *
     * @param  \App\Models\HourlyConsumption  $hourlyConsumption
     * @return void
     */
    public function updated(HourlyConsumption $hourlyConsumption): void
    {
        $date = Carbon::parse($hourlyConsumption->date);

        if ($date->lte(now())) {
            HourlyConsumptionData::dispatch($hourlyConsumption);
        }
    }
}

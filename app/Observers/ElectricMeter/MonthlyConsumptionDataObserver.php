<?php

namespace App\Observers\ElectricMeter;

use App\Events\Lorawan\ElectricMeter\MonthlyConsumptionData;
use App\Models\MonthlyConsumption;

class MonthlyConsumptionDataObserver
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // created        (1.0) Handle the MonthlyConsumption "created" event.
    // updated        (2.0) Handle the MonthlyConsumption "updated" event.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> created
     * <Function> Handle the MonthlyConsumption "created" event.
     *
     * @param  \App\Models\MonthlyConsumption  $monthlyConsumption
     * @return void
     */
    public function created(MonthlyConsumption $monthlyConsumption): void
    {
        MonthlyConsumptionData::dispatch($monthlyConsumption);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name>updated
     * <Function> Handle the MonthlyConsumption "updated" event.
     *
     * @param  \App\Models\MonthlyConsumption  $monthlyConsumption
     * @return void
     */
    public function updated(MonthlyConsumption $monthlyConsumption): void
    {
        if ($monthlyConsumption->isDirty(['date', 'consumption'])) {
            MonthlyConsumptionData::dispatch($monthlyConsumption);
        }
    }
}

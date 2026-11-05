<?php

namespace App\Models;

use App\Observers\ElectricMeter\HourlyConsumptionDataObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([HourlyConsumptionDataObserver::class])]
class HourlyConsumption extends Model
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // TYPE_ELECTRIC_METER      (1.0) Plug device type for energy consumption
    // $guarded                 (2.0) The fields that are not mass assignable.
    // scopeOfType              (3.0) Scope query to fetch data with the selected type.
    // $casts                   (4.0) The attributes that should be cast.
    // device                   (5.0) The Lorawan devices associated with the
    //                                hourly consumption
    // prunable                 (6.0) Get the prunable model query.

    use Prunable, HasFactory;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> TYPE_ELECTRIC_METER
     * <Function> Plug device type for energy consumption
     *
     * @var string
     */
    public const TYPE_ELECTRIC_METER = 'electric meter';


    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $guarded
     * <Function> The fields that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'consumption' => 'array'
    ];

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> device
     * <Function> The Lorawan devices associated with the hourly consumption
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(LorawanDevice::class, 'device_id', 'id');
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> prunable
     * <Function> Get the prunable model query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable(): Builder
    {
        return static::where('date', '<=', now()->subYear());
    }
}

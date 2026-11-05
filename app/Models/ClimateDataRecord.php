<?php

namespace App\Models;

use App\Observers\ClimateDataRecordObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([ClimateDataRecordObserver::class])]
class ClimateDataRecord extends Model
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $guarded         (1.0) Attributes that are not mass assignable.
    // $table           (2.0) The table this model maps to.
    // $casts           (3.0) Attribute casting definitions.
    // device           (4.0) The LoRaWAN device associated with the record.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $guarded
     * <Function> Attributes that are not mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $table
     * <Function> The table this model maps to.
     *
     * @var string
     */
    protected $table = 'climate_data';

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $casts
     * <Function> Attribute casting definitions.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'datetime',
    ];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> device
     * <Function> The LoRaWAN device associated with the record.
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(LorawanDevice::class, 'device_id', 'id');
    }
}

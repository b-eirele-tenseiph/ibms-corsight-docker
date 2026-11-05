<?php

namespace App\Models;

use App\Models\Floor;
use App\Traits\Scopes\FloorFilterable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LorawanGateway extends Model
{
    use HasFactory, HasUuids, FloorFilterable;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $fillable        (1.0) The attributes that are mass assignable.
    // devices          (2.0) The LoRaWAN devices associated to the LoRaWAN
    //                        gateway.
    // floor            (3.0) The floor the room belongs to.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $fillable
     * <Function> The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['ip_address', 'mac_address', 'connection', 'floor_id', 'created_at'];


    /**
     * <Layer number> (2.0)
     *
     * <Processing name> devices
     * <Function> The LoRaWAN devices associated to the LoRaWAN gateway.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function devices(): HasMany
    {
        return $this->hasMany(LorawanDevice::class, 'gateway_id', 'id');
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> floor
     * <Function> The floor the room belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }
}

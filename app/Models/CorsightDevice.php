<?php

namespace App\Models;

use App\Models\{Room, RoomArea};
use App\Traits\Scopes\RoomFilterable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class CorsightDevice extends Model
{
    use HasFactory, HasUuids, RoomFilterable;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $table              (1.0)  The table associated with the model.
    // $guarded            (2.0)  The attributes that are not mass assignable.
    // $casts              (3.0)  The attributes that should be cast.
    // room                (4.0)  The room associated with the Corsight
    //                            device.
    // roomArea            (5.0)  The room area associated with the Corsight
    //                            device.
    // data                (6.0)  The data collection of the Corsight device.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $table
     * <Function> The table associated with the model.
     *
     * @var string
     */
    protected $table = 'corsight_device';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $guarded
     * <Function> The attributes that are not mass assignable.
     *
     * @var array<int,string>
     */
    protected $guarded = [];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'enable_data_storage' => 'boolean',
    ];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> room
     * <Function> The room associated with the Corsight device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> roomArea
     * <Function> The room area associated with the Corsight device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function roomArea(): BelongsTo
    {
        return $this->belongsTo(RoomArea::class, 'room_area_id', 'id');
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> data
     * <Function> The data collection of the Corsight device.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function data(): HasMany
    {
        return $this->hasMany(CorsightDeviceData::class, 'device_id', 'id');
    }
}

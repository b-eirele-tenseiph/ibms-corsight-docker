<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CorsightDeviceData extends Model
{
    use HasFactory;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $table           (1.0)  The table associated with the model.
    // $timestamps      (2.0)  Indicates if the model should be timestamped.
    // $fillable        (3.0)  The attributes that are mass assignable.
    // $casts           (4.0)  The attributes that should be cast.
    // device           (5.0)  The Corsight device associated with the data.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $table
     * <Function> The table associated with the model.
     *
     * @var string
     */
    protected $table = 'corsight_device_data';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $timestamps
     * <Function> Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $fillable
     * <Function> The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'event_id',
        'camera_id',
        'device_id',
        'event_type',
        'context',
        'received_at',
    ];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'context'     => 'array',
        'received_at' => 'datetime',
    ];

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> device
     * <Function> The Corsight device associated with the data.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(CorsightDevice::class, 'device_id', 'id');
    }
}

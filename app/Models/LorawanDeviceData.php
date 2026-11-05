<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LorawanDeviceData extends Model
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // TRANSMISSION_UPLINK    (1.0) The data_transmission attribute for data
    //                              that where sent from the device.
    // TRANSMISSION_DOWNLINK  (2.0) Indicates the type of transmission for the
    //                              data.
    // $timestamp             (3.0) Indicates if the model should be
    //                              timestamped.
    // $fillable              (4.0) The attributes that are mass assignable.
    // $casts                 (5.0) The attributes that should be cast.
    // device                 (6.0) The LoRaWAN device associated with the data.

    use Prunable, HasFactory;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> TRANSMISSION_UPLINK
     * <Function> The data_transmission attribute for data that where sent from
     *            the device.
     *
     * @var string
     */
    public const TRANSMISSION_UPLINK = 'uplink';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> TRANSMISSION_DOWNLINK
     * <Function> The data_transmission attribute for data that where sent to
     *            the device.
     *
     * @var string
     */
    public const TRANSMISSION_DOWNLINK = 'downlink';

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $timestamps
     * <Function> Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> $fillable
     * <Function> The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'device_id',
        'type',
        'context',
        'transmission',
        'received_at',
    ];

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'context' => 'array',
        'received_at' => 'datetime',
    ];

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> device
     * <Function> The LoRaWAN device associated with the data.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(LorawanDevice::class, 'device_id', 'id');
    }
}

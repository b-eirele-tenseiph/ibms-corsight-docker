<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DeviceGridPosition extends Model
{
    use HasFactory;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // CLASSES          (1.0) Mapping of device identifiers to their
    //                        corresponding class implementations.
    // $guarded         (2.0) The attributes that are not mass assignable.
    // $casts           (3.0) The attributes that should be cast.
    // device           (4.0) Get the parent device of the grid position.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> CLASSES
     * <Function> Mapping of device identifiers to their corresponding class implementations.
     *
     * @var array
     */
    public const CLASSES = [
        'lorawanDevice' => LorawanDevice::class,
        'switchBotDevice' => SwitchBotDevice::class,
        'switchBotRemote' => SwitchBotRemote::class,
    ];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $guarded
     * <Function> The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'context' => 'array'
    ];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> device
     * <Function> Get the parent device of the grid position.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function device(): MorphTo
    {
        return $this->morphTo();
    }
}

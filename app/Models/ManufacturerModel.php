<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManufacturerModel extends Model
{
    use HasFactory;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // MILESIGHT_MODELS   (1.0) The manufacturer models for MileSight.
    // NETVOX_MODELS      (2.0) The manufacturer models for NetVox.
    // ABEEWAY_MODELS     (3.0) The manufacturer models for Abeeway.
    // REMOTELOCK_MODELS  (4.0) The manufacturer models for RemoteLOCK.
    // ACREL_MODELS       (5.0) The manufacturer models for Acrel.
    // $fillable          (6.0) The attributes that are mass assignable.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> MILESIGHT_MODELS
     * <Function> The manufacturer models for MileSight.
     *
     * @var string[]
     */
    const MILESIGHT_MODELS = ['WS501', 'WS502', 'WS503', 'UC300', 'VS121', 'WS202'];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> NETVOX_MODELS
     * <Function> The manufacturer models for NetVox.
     *
     * @var string[]
     */
    const NETVOX_MODELS = ['R312A', 'R313A', 'RA0715', 'RB11E', 'R602B', 'RA02A'];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> NETVOX_MODELS
     * <Function> The manufacturer models for Abeeway.
     *
     * @var string[]
     */
    const ABEEWAY_MODELS = ['MicroTracker', 'SmartBadge', 'CompactTracker'];

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> REMOTELOCK_MODELS
     * <Function> The manufacturer models for RemoteLOCK.
     *
     * @var string[]
     */
    const REMOTELOCK_MODELS = ['E06'];

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> ACREL_MODELS
     * <Function> The manufacturer models for Acrel.
     *
     * @var string[]
     */
    const ACREL_MODELS = ['ADW300'];

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> $fillable
     * <Function> The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['manufacturer_id', 'name'];
}

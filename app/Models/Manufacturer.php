<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Manufacturer extends Model
{
    use HasFactory;
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // MFR_NETVOX       (1.0) The manufacturer name for NetVox.
    // MFR_ABEEWAY      (2.0) The manufacturer name for Abeeway.
    // MFR_MILESIGHT    (3.0) The manufacturer name for MileSight.
    // MFR_REMOTELOCK   (4.0) The manufacturer name for RemoteLOCK.
    // MFR_ACREL        (5.0) The manufacturer name for Acrel.
    // $fillable        (6.0) The attributes that are mass assignable.
    // lorawanDevices   (7.0) The LoRaWAN devices associated to the
    //                          manufacturer.
    // models           (8.0) The manufacturer models associated to the
    //                          manufacturer.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> MFR_NETVOX
     * <Function> The manufacturer name for NetVox.
     *
     * @var string
     */
    const MFR_NETVOX = 'netvox';

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> MFR_ABEEWAY
     * <Function> The manufacturer name for Abeeway.
     *
     * @var string
     */
    const MFR_ABEEWAY = 'abeeway';

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> MFR_MILESIGHT
     * <Function> The manufacturer name for MileSight.
     *
     * @var string
     */
    const MFR_MILESIGHT = 'milesight';

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> MFR_REMOTELOCK
     * <Function> The manufacturer name for RemoteLOCK.
     *
     * @var string
     */
    const MFR_REMOTELOCK = 'remotelock';

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> MFR_ACREL
     * <Function> The manufacturer name for Acrel.
     *
     * @var string
     */
    const MFR_ACREL = 'acrel';

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> $fillable
     * <Function> The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = ['name', 'prefix_regex'];

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> lorawanDevices
     * <Function> The LoRaWAN devices associated to the manufacturer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function lorawanDevices(): HasMany
    {
        return $this->hasMany(LorawanDevice::class, 'manufacturer_id', 'id');
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> models
     * <Function> The manufacturer models associated to the manufacturer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function models(): HasMany
    {
        return $this->hasMany(ManufacturerModel::class, 'manufacturer_id', 'id');
    }
}

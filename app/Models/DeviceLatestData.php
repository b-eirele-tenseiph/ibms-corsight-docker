<?php

namespace App\Models;

use App\Observers\Device\DeviceLatestDataObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[ObservedBy([DeviceLatestDataObserver::class])]
class DeviceLatestData extends Model
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $guarded         (1.0) The attributes that are not mass assignable.
    // $casts           (2.0) The attributes that should be cast.
    // device           (3.0) Get the parent device of the latest record data.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $guarded
     * <Function> The attributes that are not mass assignable.
     *
     * @var string[]
     */
    protected $guarded = [];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast.
     *
     * @var array<string,string>
     */
    protected $casts = [
        'context' => 'array',
    ];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> device
     * <Function> Get the parent device of the latest record data.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function device(): MorphTo
    {
        return $this->morphTo();
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Observers\SwitchBotLock\SwitchBotLockAccessDataObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([SwitchBotLockAccessDataObserver::class])]

class SwitchBotLockAccess extends Model
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $guarded    (1.0) The attributes that are mass assignable.
    // $casts      (2.0) The attributes that should be cast.
    // user        (3.0) The user associated with the switchBotLock access.
    // device      (4.0) The device associated with the switchBotLock access.

    use HasFactory, HasUuids;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $guarded
     * <Function> The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $casts
     * <Function> The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'arrival_time' => 'datetime',
        'departure_time' => 'datetime',
    ];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> user
     * <Function> The user associated with the switchBotLock access.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> device
     * <Function> The device associated with the switchBotLock access.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function device()
    {
        return $this->belongsTo(SwitchBotDevice::class, 'device_id');
    }
}

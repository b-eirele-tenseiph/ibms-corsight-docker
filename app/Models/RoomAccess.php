<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Prunable;

class RoomAccess extends Model
{
    use HasFactory, HasUuids, Prunable;

    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $guarded              (1.0) All attributes are mass assignable.
    // $with                 (2.0) Relations automatically eager loaded.
    // user                  (3.0) The user linked to this room access.
    // room                  (4.0) The room associated with this access.
    // switchBotLockAccess   (5.0) The data collection of the SwitchBot Lock
    //                             Access.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $guarded
     * <Function> All attributes are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $with
     * <Function> Relations to eager load on every query.
     *
     * @var array<string>
     */
    protected $with = ['user'];

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> user
     * <Function> The user linked to this room access.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user(): belongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> room
     * <Function> The room associated with this access.
     *
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function room(): belongsTo
    {
        return $this->belongsTo(Room::class, 'room_id', 'id');
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> switchBotLockAccess
     * <Function> The data collection of the SwitchBot Lock Access.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function switchBotLockAccess()
    {
        return $this->hasOne(SwitchBotLockAccess::class, 'user_id', 'user_id')->ofMany('created_at');
    }
}
